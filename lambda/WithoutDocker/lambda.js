console.log('Loading function');

var fs = require('fs');
var async = require('async');
var aws = require('aws-sdk');
var s3 = new aws.S3({ apiVersion: '2006-03-01' });
var sqs = new aws.SQS({apiVersion: '2012-11-05'});
var ec2 = new aws.EC2({apiVersion: '2014-10-01'});


// Check if the given key suffix matches a suffix in the whitelist. Return true if it matches, false otherwise.
exports.checkS3SuffixWhitelist = function(key, whitelist) {
    if(!whitelist){ return true; }
    if(typeof whitelist == 'string'){ return key.match(whitelist + '$') }
    if(Object.prototype.toString.call(whitelist) === '[object Array]') {
        for(var i = 0; i < whitelist.length; i++) {
            if(key.match(whitelist[i] + '$')) { return true; }
        }
        return false;
    }
    console.log(
        'Unsupported whitelist type (' + Object.prototype.toString.call(whitelist) +
            ') for: ' + JSON.stringify(whitelist)
    );
    return false;
};


exports.handler = function(event, context) {
    //console.log('Received event:', JSON.stringify(event, null, 2));
    console.log('Received event:');
    
    //Read in the configuration file
    var config = JSON.parse(fs.readFileSync('config.json', 'utf8'));
    if(!config.hasOwnProperty('s3_key_suffix_whitelist')) {
        config.s3_key_suffix_whitelist = false;
    }
    console.log('Config: ' + JSON.stringify(config));

    var name = event.Records[0].s3.object.key;
    if(!exports.checkS3SuffixWhitelist(name, config.s3_key_suffix_whitelist)) {
        context.fail('Suffix for key: ' + name + ' is not in the whitelist');
    }

    var bucket = event.Records[0].s3.bucket.name;
    var key = decodeURIComponent(event.Records[0].s3.object.key.replace(/\+/g, ' '));
    var instanceId;
    
    //Sending the image key as a message to SQS and starting a new instance on EC2
    async.waterfall([
	//Get the object from the event and show its key
	function (next) {
            var params = {
		Bucket: bucket,
		Key: key
	    };
	    s3.getObject(params, function(err, data) {
		if (err) {
		    console.log(err);
		    var message = "Error getting object " + key + " from bucket " + bucket +
			". Make sure they exist and your bucket is in the same region as this function.";
		    console.log(message);
		    context.fail(message);
		} else {
		    console.log('CONTENT TYPE:', key);
		    //context.succeed(key);
		}
		next(err);
        });
	},
	//adds message to queue
	function (next){
            var params = {
		MessageBody: JSON.stringify(event),
		QueueUrl: config.queue
            };
            console.log("SENDING MESSAGE TO QUEUE");
            sqs.sendMessage(params, function (err, data) {
		if (err) { console.warn('Error while sending message: ' + err); }
		else { console.info('Message sent, ID: ' + data.MessageId); }
		next(err);
            });
	},
	//start new instance
	function (next) {
            console.log("INITIALIZING EC2");
	    
	    var params = {
	        DryRun: false,
	        ImageId: 'ami-2de00f4d',
	        InstanceType: 't2.small', //'c4.4xlarge',
	        MinCount: 1, MaxCount: 1,
	        KeyName: 'malpem',
	        UserData: new Buffer("init-script.sh").toString("base64"), 
	        SecurityGroups: ['ForMalpemEC2s']
            };
	    
            ec2.runInstances(params, function(err,data) {
		if (err) { console.log("Could not create instance", err); return; }
		
		instanceId = data.Instances[0].InstanceId;
		console.log("Started instance", instanceId);
		
		// Add tags to the instance
		params = {Resources: [instanceId], Tags: [
                    {Key: 'Name', Value: 'instanceName'}
                ]};
                ec2.createTags(params, function(err) {
                    console.log("Tagging instance", err ? "failure" : "success");
                });
		
		context.done(err,data);
	    });
            console.log("Here");
            //context.done(null);
	}
	
    ], function(err) {
        if (err) {
            context.fail('An error has occurred: ' + err);
        }
        else {
            context.succeed('Successfully processed Amazon S3 URL.');
        }
    });
};


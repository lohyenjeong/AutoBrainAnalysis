console.log('Loading function');

var fs = require('fs');
var async = require('async');
var aws = require('aws-sdk');
var s3 = new aws.S3({ apiVersion: '2006-03-01' });
var sqs = new aws.SQS({apiVersion: '2012-11-05'});
var ec2 = new aws.EC2({apiVersion: '2014-10-01'});
var script = fs.readFileSync('userdata', 'utf8');



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


    // Get the object from the event and show its key
    var bucket = event.Records[0].s3.bucket.name;
    var key = decodeURIComponent(event.Records[0].s3.object.key.replace(/\+/g, ' '));
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
    });


    var instanceId;
    
    //Sending the image key as a message to SQS and starting a new instance on EC2
    async.waterfall([
        function(next){
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
        function(next) {
            var job = event.Records[0].s3.object.key;
            console.log("Job is: " + job);
            var text = 'home/ubuntu/malpem-1.2/bin/malpem-proot -i home/ubuntu/in/' + job + '-o home/ubuntu/out -t 16\n';
            console.log("Command to run is: " + text);
            script = script + text;
            console.log("Full Script: \n" + script);

            console.log("INITIALIZING EC2");
	        
	        var params = {
		    ImageId: 'ami-545ca134',
		    InstanceType: 'c4.4xlarge', //'c4.4xlarge', //t2.micro,
		    MinCount: 1, MaxCount: 1,
            KeyName: 'malpem',
            UserData : new Buffer(script).toString('base64')
            };
	        
            ec2.runInstances(params, function(err,data) {
		if (err) { console.log("Could not create instance", err); return; }
		console.log("Created new EC2 instance");
		
		instanceId = data.Instances[0].InstanceId;
		console.log("Started instance", instanceId);
		    });
        },
	//wait until new instance is running
	function(next) {
	        console.log("Checking ec2 status...");
	        
	        var params = {
		    InstanceIds: instanceId,
		        };
	        
	        ec2.describeInstances(params, function(err, data) {
		    if (err) console.log(err, err.stack); // an error occurred
		    else     console.log(data);           // successful response

		    function check_status() {
			    var status = data.Instances[0].state;
			    
			    if (status == "running") {
				console.log("Instance created with id: " + instanceId);
				    } else
					setTimeout(check_status, 250);   
			}
		        });
	    },
	

	//terminate new instance
	function(next) {
	        console.log("TERMINATING EC2");
	        
	        var params = {
		    InstanceIds: instanceId,
		        };
	        
	        ec2.terminateInstances(params, function(err, data) {
		    if (err) console.log(err, err.stack); // an error occurred
		    else     console.log(data);           // successful response
		        });
	    }
	
    ], function(err){
        if (err) {
            context.fail('An error has occurred: ' + err);
        }
        else {
            context.succeed('Successfully processed Amazon S3 URL.');
        }
    }
		       );

};


//"bin/malpem-proot -i " +  key + " -o outputDir -t 16"
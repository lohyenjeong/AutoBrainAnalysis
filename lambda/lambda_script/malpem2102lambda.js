console.log('Loading function');

var fs = require('fs');
var async = require('async');
var aws = require('aws-sdk');
var s3 = new aws.S3({ apiVersion: '2006-03-01' });
var sqs = new aws.SQS({apiVersion: '2012-11-05'});
var ecs = new aws.ECS({apiVersion: '2014-11-13'});
//var ec2 = new aws.EC2({apiVersion: '2014-10-01'});

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
        context.fail('Suffix for key: ' + name + ' is not in the whitelist')
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
            context.succeed(key);
        }
    });


 //Sending the image key as a message to SQS and starting the ECS task
    async.waterfall([
        function(next){
            var params = {
                MessageBody: JSON.stringify(event),
                QueueUrl: config.queue
            };
            console.log("IN QUEUE FUNCTION");
            sqs.sendMessage(params, function (err, data) {
                    if (err) { console.warn('Error while sending message: ' + err); }
                    else { console.info('Message sent, ID: ' + data.MessageId); }
                    next(err);
            });
        }, 
        function (next) {
            console.log("INITIALIZING ECS");
            var params = {
                taskDefinition: config.task,
                count: 1
            };

            ecs.runTask(params, function(err,data){
                if(err){console.warn('error: ', "Error while starting task: " + err);}
                else{console.info('Task ' + config.task + ' started: ' + JSON.stringify(data.tasks))} next(err);
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


            /*Unnecessary:
            var params = {
                ImageId: 'ami-e559b485', // Amazon Linux AMI x86_64 EBS
                InstanceType: 't1.micro',
                MinCount: 1, MaxCount: 4
            };

            // Create the instance
            ec2.runInstances(params, function(err, data) {
                if (err) { console.log("Could not create instance", err); return; }
                console.log("Here");

                var instanceId = data.Instances[0].InstanceId;
                console.log("Created instance", instanceId);

                 // Add tags to the instance
                params = {Resources: [instanceId], Tags: [
                    {Key: 'Name', Value: 'instanceName'}
                ]};
                ec2.createTags(params, function(err) {
                    console.log("Tagging instance", err ? "failure" : "success");
                });
            });
            */

            //"bin/malpem-proot -i " +  key + " -o outputDir -t 16"

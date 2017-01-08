//To upload to AWS console, must be in zip file with config.json and the node_modules directory 
//(download from https://nodejs.org/en/) including the Async.js library

// A Lamdba function to forward event data (.nii.gz image uploaded to S3 bucket) into an 
// SQS queue.

//add event source in aws console to trigger function when image
//uploaded to s3 input bucket

//give s3 bucket permission to invoke lambda function in function's 
//resource policy

//START OF FUNCTION
console.log('Lambda function starting...');

//dependencies
var fs = require('fs');                                                              
var async = require('async');                                                        
var aws = require('aws-sdk');                                                        

//references to clients
var s3 = new aws.S3();
var sqs = new aws.SQS();                                   
//var as_group  = new aws.AutoScaling();

//get s3 event data //should be inputs.handler?
exports.handler = function(event, context) {
    console.log('Received event:');                                                  
    console.log(JSON.stringify(event, null, '  '));

    // Read options from the event.
    var input_bucket = event.Records[0].s3.bucket.name;
    // Object key may have spaces or unicode non-ASCII characters.
    var srcKey = decodeURIComponent(event.Records[0].s3.object.key.replace(/+/g, " "));
    // prepare SQS message
    var params = {
	MessageBody: 'object '+ srcKey + ' ',
	QueueUrl: 'https://sqs.us-west-2.amazonaws.com//malpemjobqueue',
	DelaySeconds: 0
    };
    // send SQS message
    sqs.sendMessage(params, function (err, data) {
	    if (err) {
		console.error('Unable to put object' + srcKey + ' into SQS queue due to an error: ' + err);
		context.fail(srcKey, 'Unable to send message to SQS');
		    }
	    else {
		console.log('Message sent:' + data.messageId);
		    }
	});
   
    //trigger auto scaling group
    //use SNS to get name of group/instance?
    
    //run child process/shell script, malpem on new instance
    
    //when malpem complete, output report file to s3 output bucket
    
    //terminate instances
    
    //send message saying job complete?
    
    console.log('Lambda function complete. Terminating.');
};  
   



//var childProcess = require("child_process");
/*childProcess.exec(eg "mkdir /tmp/bin; cp /var/task/bin/ffmpeg /tmp/bin/ffmpeg; chmod 755 /tmp/bin/ffmpeg",
   function (error, stdout, stderr) {
        if (error) {
          console.log("error: " + error)
        } else {
          def.resolve(result);
        }
      }
    )*/

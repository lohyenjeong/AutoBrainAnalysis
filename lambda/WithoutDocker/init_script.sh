#!/bin/bash

region=us-west-2
queue=https://us-west-2/queue.amazonaws.com/721970247246/MalpemQueue

echo "Fetching messages from SQS queue: ${queue}..."
result=$( \
    aws sqs receive-message \
        --queue-url ${queue} \
        --region ${region} \
        --wait-time-seconds 20 \
        --query Messages[0].[Body,ReceiptHandle] \
        | sed -e 's/^"\(.*\)"$/\1/'\
      )

echo "Message: ${result}."

receipt_handle=$(echo ${result} | sed -e 's/^.*"\([^"]*\)"\s*\]$/\1/')
echo "Receipt handle: ${receipt_handle}."

bucket=$(echo ${result} | sed -e 's/^.*arn:aws:s3:::\([^\\]*\)\\".*$/\1/')
echo "Bucket: ${bucket}."

key=$(echo ${result} | sed -e 's/^.*\\"key\\":\s*\\"\([^\\]*\)\\".*$/\1/')
echo "Key: ${key}."

cd malpem-1.2
mkdir files

echo "Copying ${key} from S3 bucket ${bucket}..."
aws s3 cp s3://${bucket}/${key} files

echo "Deleting message..."
aws sqs delete-message \
    --queue-url ${queue} \
    --region ${region} \
    --receipt-handle "${receipt_handle}"

exit 0

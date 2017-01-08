# Automatic Brain Analysis using MALPEM
This is the git repository for our group project. 

We are aiming to produce an online portal in which users can upload brain scans,
which are then analysed by the MALPEM software hosted on the Imperial cloud or
AWS cloud. The reports are available for download from the website.

Code found here includes scripts / functions to run on the cloud, 
PHP modules for the website and templates.

## Installation
A full set of setup instructions are included, under Documentation.

## Team Members
Zhi Ting Lim zhi.lim15@imperial.ac.uk  
Lizzie Corrie elizabeth.corrie15@imperial.ac.uk  
Lucy Bates lucy.bates15@imperial.ac.uk  
Phutthaphon Suvanrak phutthaphon.suvanrak15@imperial.ac.uk  
Yen Jeong Loh yen.loh15@imperial.ac.uk  
Olivier Khatib olivier.khatib14@imperial.ac.uk  

# File Descriptions
## aws_templates
The start of a template for use with AWS CloudFormation, which we decided 
would not suit our needs.

## dockerfile_code
The script for setting up a Docker image. 

## joomla-t
The template for our Joomla website. Written in CSS, based on Twitter
bootstrap for mobile compatibility.

## lambda
Three lambda functions that were tested but two of which were not successful. 
The unsuccessful ones are 'WithAnsible', which is written in python and meant 
for use with an Ansible script, the other is 'WithoutDocker' which is
written in json. 'lambda_script' contains 'malpem2102lambda.js' which was 
successful except that the script included as user data does not run on the new
EC2 instance.

## lambda_script
The files that must be put in a zip file with a file containing the main
code for the Lambda function before it is uploaded to AWS. Include a 
configuration file, Node.js modules and the script to be run at launch.

## mod_awsupload
The Joomla module for uploading a brain scan for processing on AWS.
Adapted from SimpleFileUpload v1.3.5 for Joomla, available here: 
http://extensions.joomla.org/extension/simple-file-upload 

## mod_imperialupload
The Joomla module for uploading a brain scan for processing on the DoC cloud.
Adapted from SimpleFileUpload v1.3.5 for Joomla, available here: 
http://extensions.joomla.org/extension/simple-file-upload

## mod_malpemadminview
The Joomla module for the administrator view.

## mod_malpemviewer
The Joomla module that allows users to view the status of their reports,
download completed reports, and delete all reports.

## prototype_website
The website prototype, created to work out the functionality we would need,
and appearance we required in our final product. No actual functionality.

## shell_scripts
Scripts run on the servers to check for files and take necessary action.

## test_scripts
Scripts used in code testing for correctness and coverage.



#!/usr/bin/ python3
import shutil
import os
import time
import MySQLdb
import smtplib
import gc

### TO RUN IN BACKGROUND: USE
# nohup /<path_to>/check_outputs.py &

###STATIC FINAL VARIABLES
#time interval to check for new files (in seconds) 
t = 15
#set the directories
inDir = "/var/www/html/joomla/ul_scans"
outDir = "/var/www/html/joomla/imperialoutput"
oldFileList = "/tmp/filelist.txt"

#open oldFileList to clear (truncate) it or make new file if it doesn't exist
f = open(oldFileList, 'w')
f.close()

# garbage collection for unclosed db connection (just in case)
gc.collect()

###DATABASE VARIABLES 
db = MySQLdb.connect(host="localhost",    # host, usually localhost
                     user="root",         # username
                     passwd="malpemmsc",  # password
                     db="joomlademo")     # name of database

# cursor object: lets us execute queries
cur = db.cursor()
query = "UPDATE r17ie_user_jobs SET output_path=%s, time_ended=%s WHERE expected_output=%s"
inFileNameQuery = "SELECT file_path FROM r17ie_user_jobs WHERE expected_output=%s"
queryEmail = "SELECT user_email FROM r17ie_user_jobs WHERE expected_output=%s"
# format for executing queries: cur.execute("SELECT...")

# email settings
# message body needs an initial '\n' otherwise it won't appear in the final email
fromAddr = "malpemimperial@gmail.com"
emailMsg1 = "\nYour file has finished running on Malpem! Your results are now ready to be viewed (output filename: "
emailMsg2 = "). Please visit the website to download the report."
emailUser = "malpemimperial"
emailPw = "malpemmsc"

while True:
  f = open(oldFileList, 'r+')
  #enter file names from f into a list (one file name per line)
  oldFiles = [line.rstrip('\n') for line in f]
  #list current files in directory
  currFiles = os.listdir(outDir)
  #look for new report files
  for fileName in currFiles:
    if fileName not in oldFiles:
      #check database to see if we have been waiting for this file
      #enter output_path and current time (time ended)
      outputPath = outDir + "/" + fileName
      timeEnded = time.strftime('%Y-%m-%d %H:%M:%S')
      #retval value is number of rows changed
      retval = cur.execute(query, (outputPath, timeEnded, fileName))
      #actually commit changes to database
      db.commit()
      print(retval)

      #append file to oldFileList
      # print(fileName)
      f.write(fileName)
      f.write("\n")

      #delete user's input images
      try:
        #find input image path (for debugging purposes)
        cur.execute(inFileNameQuery, fileName)
        data = cur.fetchall()
        inFileName = data[0][0]
        os.remove(inFileName)
        print("Deleted user's image file: %s" % (inFileName))
      except:
        print("Error: failed to delete user's input file.")

      #change file permissions - THESE NEED FIXING
#      os.chown(outputPath,1000,1000)
 #     os.chmod(outputPath,777)
      #email the user
      cur.execute(queryEmail, fileName)
      data = cur.fetchall()
      #should return [[email1], [email2], ...], 
      #but should only have 1 email (only 1 row should match expected_output)
      toAddr = data[0][0]
      #print("email:")
      #print(toAddr)
      #send email
      try:
        server = smtplib.SMTP('smtp.gmail.com:587')
        server.ehlo()
        server.starttls()
        server.login(emailUser,emailPw)
        msg = emailMsg1 + fileName + emailMsg2
        server.sendmail(fromAddr, [toAddr], msg)
        server.quit() 
        print("Sent email to: ")
        print(toAddr)
      except:
        print("Error: failed to send email")
  # close file
  f.close()
  # time to wait between checks
  time.sleep(t)

#!/usr/bin/ python3

import shutil
import os
import subprocess
import time

#### INSTRUCTIONS if starting system from startup
# 1) mount input folder
# 2) mount output folder
# 3) run this script with sudo

#malpemCommand = "~/malpem-1.2/bin/malpem-proot -i %s -o ~/malpem-1.2/outputDir -t 8 -c"

inDir = "imperialinput"
outDir = "imperialoutput"
oldFileList = "/tmp/imperialinputfilelist.txt"
oldDirList = "/tmp/imperialinputdirlist.txt"
malpemOutputDir = "/opt/malpem-1.2/outputDir"

#time in seconds to sleep between iterations
t = 10
#open oldFileList/oldDirList to clear (truncate) it or make new file if it doesn't exist
f = open(oldFileList, 'w')
f.close()
f_dir = open(oldDirList, 'w')
f_dir.close()

while (True):

  f = open(oldFileList, 'r+')
  f_dir = open(oldDirList, 'r+')
  #enter file names from f into a list (one file name per line)
  oldFiles = [line.rstrip('\n') for line in f]
  print("##Previous files:")
  print(oldFiles)
  #list current files in directory
  currFiles = os.listdir(inDir)
  #look for new files
  for fileName in currFiles:
    if fileName not in oldFiles:
      suffix = '.nii.gz'
      if fileName.endswith(suffix) == False :      
        f.write(fileName)
        f.write("\n")
        continue
      fileFullName = inDir + "/" + fileName
      #change permissions of file so it can be copied by Malpem
      #os.chmod(fileFullName, 0o777)
      return_code = subprocess.call("sudo chmod 777 %s"%(fileFullName), shell=True)
      print("##File being processed: " +fileName)
      #run Malpem on this file
      #return_code = 0
      return_code = subprocess.call(['malpem-1.2/bin/malpem-proot','-i',fileFullName,'-o','malpem-1.2/outputDir', '-t', '15', '-c'])
      print ("##Malpem call return code: ")
      print(return_code)

      #enter dir names from f_dir into a list (one dir name per line)
      oldDir = [line.rstrip('\n') for line in f_dir]
      #if return_code==0?
      currDir = os.listdir(malpemOutputDir)
      for dirName in currDir:
        if dirName not in oldDir:

          workingDir = malpemOutputDir+"/"+dirName
          newFolder = workingDir+"/"+dirName
          #print(newFolder)
          os.mkdir(newFolder,0777);

          filesInDir = os.listdir(malpemOutputDir+"/"+dirName)
          for newReport in filesInDir:
            if newReport.endswith("_Report.pdf"):
              print("##Report name: "+newReport)
              oldFile = malpemOutputDir+"/"+dirName+"/"+newReport
              shutil.copy2(oldFile,newFolder)
            elif newReport.endswith("_MALPEM_tissues.nii.gz"):
              print("##_MALPEM_tissues name: "+newReport)
              oldFile = malpemOutputDir+"/"+dirName+"/"+newReport
              shutil.copy2(oldFile,newFolder)
            elif newReport.endswith("_MALPEM.nii.gz"):
              print("##_MALPEM name: "+newReport)
              oldFile = malpemOutputDir+"/"+dirName+"/"+newReport
              shutil.copy2(oldFile,newFolder)
            elif newReport.endswith("_mask.nii.gz"):
              print("##_mask name: "+newReport)
              oldFile = malpemOutputDir+"/"+dirName+"/"+newReport
              shutil.copy2(oldFile,newFolder)
            
          filesInDir = os.listdir(malpemOutputDir+"/"+dirName+"/report")
          for csv in filesInDir:
            if csv.endswith(".csv"):
              print("##CSV name: "+csv)
              oldFile = malpemOutputDir+"/"+dirName+"/report/"+csv
              shutil.copy2(oldFile,newFolder)

          probMALPEMZip = malpemOutputDir+"/"+dirName+"/prob_MALPEM"
          shutil.make_archive(probMALPEMZip, 'zip', probMALPEMZip)
          shutil.copy2(probMALPEMZip+".zip",newFolder)

          shutil.make_archive(newFolder, 'zip', newFolder)

          folderToMove = newFolder +'.zip'
          fileNameNoExt = fileName
          fileNameNoExt = fileNameNoExt.replace(".nii.gz", "")
          zipName = fileNameNoExt+'.zip'
          print("##Output zip file:")
          print(zipName)
          s3OutBucket = outDir+"/"+zipName

          print("##Copying zip file to output folder (may take some time)")
          shutil.move(folderToMove,s3OutBucket)

          return_code = subprocess.call("sudo chmod 777 %s"%(s3OutBucket), shell=True)
          #print("##Zip file permissions changed")
          f_dir.write(dirName)
          f_dir.write("\n")

      #append file to oldFileList
      # print(fileName)
      f.write(fileName)
      f.write("\n")
  
  # close file
  f.close()
  # time to wait between checks
  print("No. of seconds to sleep:")
  print(t)
  time.sleep(t)

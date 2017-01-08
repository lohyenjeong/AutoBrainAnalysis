#!/usr/bin/ python3


import unittest
import shutil
import os
import time
import gc
import subprocess

#### INSTRUCTIONS if starting system from startup
# 1) mount input folder
# 2) mount output folder
# 3) run this script with sudo

malpemCommand = "~/malpem-1.2/bin/malpem-proot -i %s -o ~/malpem-1.2/outputDir -t 8"

inDir = "imperialinput"
outDir = "imperialoutput"
oldFileList = "/tmp/imperialinputfilelist.txt"
oldDirList = "/tmp/imperialinputdirlist.txt"
malpemOutputDir = "malpem-1.2/outputDir"

#time in seconds to sleep between iterations
t = 10
#open oldFileList/oldDirList to clear (truncate) it or make new file if it doesn't exist
f = open(oldFileList, 'w')
f.close()
f_dir = open(oldDirList, 'w')
f_dir.close()

def check_files_once():

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
      suffix = '.txt'
      if fileName.endswith(suffix) == True :
        continue
      fileFullName = inDir + "/" + fileName
      #change permissions of file so it can be copied by Malpem
      #os.chmod(fileFullName, 0o777)
      return_code = subprocess.call("sudo chmod 777 %s"%(fileFullName), shell=True)
      print("##File being processed: " +fileFullName)
      #run Malpem on this file
      #return_code = subprocess.call(malpemCommand%(fileFullName), shell=True)
      return_code = subprocess.call(['malpem-1.2/bin/malpem-proot','-i',fileFullName,'-o','malpem-1.2/outputDir', '-t', '8'])
      print ("##Malpem call return code: ")
      print(return_code)

      #enter dir names from f_dir into a list (one dir name per line)
      oldDir = [line.rstrip('\n') for line in f_dir]
      #if return_code==0?
      currDir = os.listdir(malpemOutputDir)
      for dirName in currDir:
        if dirName not in oldDir:
          filesInDir = os.listdir(malpemOutputDir+"/"+dirName)
          #find [original name]_report.pdf
          for newReport in filesInDir:
            if newReport.endswith("_Report.pdf"):
              print("##New report name: "+newReport)
              oldFile = malpemOutputDir+"/"+dirName+"/"+newReport
              newFileDest = outDir+"/"+newReport
              #moves pdf report to outDir
              shutil.move(oldFile,newFileDest)
          f_dir.write(dirName)
          f_dir.write("\n")
      #append file to oldFileList
      # print(fileName)
      f.write(fileName)
      f.write("\n")
  
  # close file
  f.close()
  # time to wait between checks
  time.sleep(t)
  return 1


class TestCheckFiles(unittest.TestCase):
  def test_check_files(self):
    result = check_files_once()
    self.assertEqual(result, 1)


if __name__ == '__main__':
  unittest.main()
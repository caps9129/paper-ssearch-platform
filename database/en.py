
import os

import shutil

scan_path = "/volume1/web/DB"

for dirNames in os.listdir(scan_path) :
    path = os.path.join(scan_path, dirNames)
    if os.path.isdir(path): 
        flag = 0

        for dirpath,dirnames,filenames in os.walk(path):
            
            for files in filenames:

                if(dirNames in files):
                    flag = 1
                
                if("en.pdf" in files):
                    ofile = files

    

        if(flag == 0):
            src_dir = os.path.join(path, ofile)
            dst_dir = os.path.join(path, dirNames + "_en.pdf")
            print(src_dir, dst_dir)
            shutil.copy(src_dir, dst_dir)

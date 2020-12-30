import pandas as pd
import numpy as np
import os
import pymysql
import re
import datetime
import shutil
from distutils.dir_util import copy_tree
import codecs
# import sys
# sys.stdout = codecs.getwriter("utf-8")(sys.stdout.detach())


if(os.path.exists('/volume1/Database/ing/journal_list.csv')):
    df = pd.read_csv("/volume1/Database/ing/journal_list.csv", encoding='utf-8')
else:
    df = pd.read_csv("/volume1/web/database/journal_list.csv", encoding='utf-8')



# dict[key:{是否全文, 文章進度, rank}]
edit_level_dict = {'pcn.pdf': [0, 1, 0], 'pcnn.pdf': [0, 2, 1], 'pcnfin.pdf': [0, 3, 2], 'fcn.pdf': [1, 1, 3], 'fcnn.pdf': [1, 2, 4], 'fcnfin.pdf': [1, 3, 5]}
scan_path = "/volume1/Database/ing"
file_dict = {'流水號': list(), '急迫程度': list(), '全文': list(), '文章進度': list(), '初稿完成日期': list(), '最近更新日期': list(), '原文link': list(), '中文link': list(), '資料夾link': list(), 'check': list(), 'check log': list()}


# scan files
for files in os.walk(scan_path):
  
 
    # 原文是否存在
    ifen = 0
    # 是否全文
    ifall = 0
    # 翻譯鏈結
    file_link = ""
    # 進度
    progess = 0
    # 時間
    startdate = "" 
    enddate = ""
    
    # 翻譯文章計數
    legal_file_count = 0
    # print(files[1])
    # if files contain en.pdf
    if(1):
        file_link = ""
        cn_file_link = ""
        # get nest level of translation pdf to store time & status info
        # print(files[2])
        for file in files[2]:

            if("en.pdf" in file):
                # print(file)
                # file_link = '/volume1/web/DB/' + file
                ifen = 1

            if(ifen == 1):
                for key_level, value_level in edit_level_dict.items():
                    if(key_level in file):
                        if(legal_file_count == 0):
                            rank = value_level[2]
                            ifall = value_level[0]
                            progess = value_level[1]
                            legal_file_count = legal_file_count + 1
                            cn_file_link = '/volume1/web/DB/' + file
                            try:
                                date = re.findall(r'((.+)(_+)(\w+)(_+)(\w+))', file)[0][-3]
                                date = datetime.datetime.strptime(date, '%Y%m%d')
                            except ValueError:
                                date = np.nan
                            startdate = date
                            enddate = date
                        else:
                            if(value_level[2] > rank):
                                rank = value_level[2]
                                ifall = value_level[0]
                                progess = value_level[1]
                                legal_file_count = legal_file_count + 1
                                cn_file_link = '/volume1/web/DB/' + file
                                date = re.findall(r'((.+)(_+)(\w+)(_+)(\w+))', file)[0][-3]
                                date = datetime.datetime.strptime(date, '%Y%m%d')
                                enddate = date
                            elif(value_level[2] == rank):
                                temp_date = re.findall(r'((.+)(_+)(\w+)(_+)(\w+))', file)[0][-3]
                                temp_date = datetime.datetime.strptime(temp_date, '%Y%m%d')
                                if(temp_date > date):
                                    enddate = temp_date
                                    legal_file_count = legal_file_count + 1
                                    cn_file_link = '/volume1/web/DB/' + file
                                else:
                                    break
                                
                            else:
                                break

                            continue

        result = re.findall(r'((/+)(\w+))', str(files[0]))
        
        if (len(result) == 5 and ifen == 1):
            needed = result[3][2]
            ID = result[4][2]

            # in some special case , prevent ignore pdf file
            legal_file_count = 0
            if(cn_file_link == ""):
                for file in files[2]:
                    # print(file)
                    for key_level, value_level in edit_level_dict.items():
                        if(key_level in file):
                            if(legal_file_count == 0):
                                rank = value_level[2]
                                ifall = value_level[0]
                                progess = value_level[1]
                                legal_file_count = legal_file_count + 1
                                cn_file_link = '/volume1/web/DB/' + file
                                date = re.findall(r'((.+)(_+)(\w+)(_+)(\w+))', file)[0][-3]
                                date = datetime.datetime.strptime(date, '%Y%m%d')
                                startdate = date
                                enddate = date
                            else:
                                if(value_level[2] > rank):
                                    rank = value_level[2]
                                    ifall = value_level[0]
                                    progess = value_level[1]
                                    legal_file_count = legal_file_count + 1
                                    cn_file_link = '/volume1/web/DB/' + file
                                    date = re.findall(r'((.+)(_+)(\w+)(_+)(\w+))', file)[0][-3]
                                    date = datetime.datetime.strptime(date, '%Y%m%d')
                                    enddate = date
                                elif(value_level[2] == rank):
                                    temp_date = re.findall(r'((.+)(_+)(\w+)(_+)(\w+))', file)[0][-3]
                                    temp_date = datetime.datetime.strptime(temp_date, '%Y%m%d')
                                    if(temp_date > date):
                                        enddate = temp_date
                                        legal_file_count = legal_file_count + 1
                                        cn_file_link = '/volume1/web/DB/' + file
                                    else:
                                        break
                                    
                                else:
                                    break

                                continue

            file_dict['原文link'].append('/volume1/web/DB/'+ ID + '/' + ID + '_en.pdf')
            file_dict['急迫程度'].append(needed)
            file_dict['流水號'].append(ID)
            file_dict['資料夾link'].append('/volume1/web/DB/'+ID)
            file_dict['初稿完成日期'].append(startdate)
            file_dict['最近更新日期'].append(enddate)
            file_dict['全文'].append(ifall)
            file_dict['中文link'].append(cn_file_link)
            file_dict['文章進度'].append(progess)
            file_dict['check'].append(1)
            file_dict['check log'].append(datetime.datetime.now())

            # print(ID, file_link, cn_file_link)


        # check if columns exists in df
        for key_column, value_column in file_dict.items():
            if(key_column not in df.columns):
                # print(key_column + "not exist!")
                df[key_column]= None

df['check'] = 0
df['全文'] = 0
df['文章進度'].fillna(0, inplace = True) 


def zeropad(row):
    row = str(row).zfill(6)
    return row

df['流水號'] = df['流水號'].apply(zeropad)

# os._exit()

for index in range(0, len(file_dict['流水號'])):
    for key, value in file_dict.items() :
        if(key != '流水號'):
            df.loc[df['流水號'] == file_dict['流水號'][index], [key]] = file_dict[key][index]

    # move file
    # print(scan_path + '/' + file_dict['急迫程度'][index] + '/' + file_dict['流水號'][index])
    src = scan_path + '/' + file_dict['急迫程度'][index] + '/' + file_dict['流水號'][index]
    copy_tree(src, "/volume1/web/DB" + '/' + file_dict['流水號'][index])


# df.to_csv("/volume1/web/database/_reading list_20191231db.csv", index=False, encoding='utf-8')
with open("/volume1/web/database/journal_list.csv", 'w', encoding='utf-8-sig', errors='replace') as f:
    df.to_csv(f, index=False, encoding='utf-8-sig')
with open("/volume1/Database/ing/journal_list.csv", 'w', encoding='utf-8-sig', errors='replace') as f:
    df.to_csv(f, index=False, encoding='utf-8-sig')
    
filepath = "/volume1/web/database/log/journal_list_" + str(datetime.date.today()) + "_complete.csv"

with open(filepath , 'w', encoding='utf-8-sig', errors='replace') as f:
    df.to_csv(f, index=False, encoding='utf-8-sig')



work_df = df[df['文章進度'] == 0]
work_df = work_df.sort_values(by=['急迫程度'])

with open("/volume1/Database/ing/work.csv", 'w', encoding='utf-8-sig', errors='replace') as f:
    work_df.to_csv(f, index=False, encoding='utf-8-sig')

print("backend complete!")
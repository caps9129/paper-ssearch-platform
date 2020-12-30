import pandas as pd
import os
import re
import json

if(os.path.exists('/volume1/Database/ing/journal_list.csv')):
    df = pd.read_csv("/volume1/Database/ing/journal_list.csv", encoding='utf-8')
else:
    df = pd.read_csv("/volume1/web/database/journal_list.csv", encoding='utf-8')
columns = ['挑選文章的老師', 'Author(s) -Full Name', 'Paper Name', 'Journal name', 'Journal Abbreviations', 'keyword']
stop_list = ['and', ',', '&', 'nan']
word_dict = list()

def split_by_space(row):
    for value in str(row).split():
        temp.append(value)

for column in columns:
    temp = list()
    df[column].map(split_by_space)
    temp = list(set(temp))
    word_dict = word_dict + temp

temp = list()
temp = word_dict
word_dict = list()
for word in temp:
    if('-' in word and '.' not in word):
        # remake and replace
        if('(' not in word ):
            o_w = word
            word = re.findall(r'((\w+))', word)
            new_word = ''
            length = len(word)
            count = 0
            for w in word:
                count = count + 1
                if(re.match(r'.*([1-3][0-9]{3})', w[0])):
                    break
                else:
                    word_dict.append(w[0])
                    if(count < length):
                        t_w = w[0] + '-'
                        new_word = new_word + t_w
                    else:
                        new_word = new_word + w[0]
            if(new_word):
                word_dict.append(new_word)

        # remake and keep
        else :
            word_dict.append(o_w)
            word = re.findall(r'((\w+))', word)
            for w in word:
                if(re.match(r'.*([1-3][0-9]{3})', w[0])):
                    break
                else:
                    word_dict.append(w[0])
    #normal situation   
    else:
        word = re.findall(r'((\w+))', word)
        for w in word:
            if(len(w[0]) > 1):
                if(re.match(r'.*([1-3][0-9]{3})', w[0])):
                    break
                else:
                    word_dict.append(w[0])

word_dic = list(set(word_dict))

with open("/volume1/web/database/wordlist.json", "w", encoding='UTF-8') as fp:   
    
    json.dump(word_dic, fp)

with open("/volume1/web/js/wordlist.js", "w", encoding='UTF-8') as fp:   
    
    fp.write("var Data = ")
    json.dump(word_dic, fp)

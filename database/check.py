import os
import pandas as pd
import numpy as np

check_path = '/volume1/web/DB/'

dir_list = sorted(os.listdir(check_path))

#
symbol = {'ID':[], 'pcn.pdf':np.zeros(len(dir_list)), 'pcnn.pdf':np.zeros(len(dir_list)), 'pcnfin.pdf':np.zeros(len(dir_list)), 'fcn.pdf':np.zeros(len(dir_list)), 'fcnn.pdf':np.zeros(len(dir_list)), 'fcnfin.pdf':np.zeros(len(dir_list)), 'error':np.zeros(len(dir_list))}

for i, dir_name in enumerate(dir_list):
    error = 0
    symbol['ID'].append(i + 1)
    path = os.path.join(check_path, dir_name)
    fl = os.listdir(path)
    for k,v in symbol.items():
        if(k != 'ID' and k != 'error'):
            for f in fl:
                if(k in f):
                    v[i] += 1
            
            if(v[i] > 1):
                symbol['error'][i] = 1

df = pd.DataFrame.from_dict(symbol)

df.to_csv('/volume1/web/database/check.csv', index=False, encoding='utf-8-sig')
df.to_csv('/volume1/Database/check.csv', index=False, encoding='utf-8-sig')


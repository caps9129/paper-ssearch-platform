import pandas as pd
import datetime as dt
import numpy as np
import seaborn as sns
import matplotlib.pyplot as plt
import copy
simple_list = ['American Economic Review', 'Economica', 'Journal of Political Economy', 'Quarterly Journal of Economics', 'Review of Economic Studies']
df = pd.read_csv('/volume1/Database/ing/journal_list.csv', encoding='utf-8')


df = df[df['最近更新日期'].notnull()]
df['最近更新日期'] = pd.to_datetime(df['最近更新日期'])
df['最近更新日期'] = df['最近更新日期'].dt.strftime('%Y-%m')
time = df.groupby(['最近更新日期']).count()


time = time['流水號'].cumsum().to_frame()
time = time.rename(columns={'流水號':'quantity of paper'})
time.index.names = ['date']
simple = copy.deepcopy(time)


for column in simple_list:

    a = df.loc[df['Journal name'] == column].groupby(['最近更新日期']).count()['流水號'].fillna(0).cumsum().to_frame().rename(columns={'流水號':column}).astype(int)
    simple = simple.merge(a, left_index=True, right_index=True, how='outer', suffixes=(False, False))

for i, v in enumerate(simple_list):
    i += 1
    simple.iloc[-1: ,i] = simple.iloc[: ,i].max()

simple.index.names = ['date']
sns.set(rc={'figure.figsize':(50,25)},palette=['#F70A0A','#2A930C','#930C85'], font_scale=1.7)
sns.lineplot(data=simple, dashes=False)
sns.set_style("darkgrid" , {"ytick.major.size": 10 , "ytick.minor.size": 2 , 'grid.linestyle': '--'})
plt.rcParams['font.sans-serif']=['SimHei'] #用来正常显示中文标签
plt.rcParams['axes.unicode_minus']=False #用来正常显示负号
plt.xticks(rotation=90)
plt.legend(bbox_to_anchor=(1.05, 1), loc=2, borderaxespad=0.)
plt.savefig(fname = '/volume1/Database/simple_summary.png', dpi=150, bbox_inches='tight')


for column in df['Journal name'].unique():

    a = df.loc[df['Journal name'] == column].groupby(['最近更新日期']).count()['流水號'].fillna(0).cumsum().to_frame().rename(columns={'流水號':column}).astype(int)
    time = time.merge(a, left_index=True, right_index=True, how='outer', suffixes=(False, False))

for i, v in enumerate(df['Journal name'].unique()):
    i += 1
    time.iloc[-1: ,i] = time.iloc[: ,i].max()

time.index.names = ['date']
sns.set(rc={'figure.figsize':(50,25)},palette=['#F70A0A','#2A930C','#930C85'], font_scale=1.7)
sns.lineplot(data=time, dashes=False)
sns.set_style("darkgrid" , {"ytick.major.size": 10 , "ytick.minor.size": 2 , 'grid.linestyle': '--'})
plt.rcParams['font.sans-serif']=['SimHei'] #用来正常显示中文标签
plt.rcParams['axes.unicode_minus']=False #用来正常显示负号
plt.xticks(rotation=90)
plt.legend(bbox_to_anchor=(1.05, 1), loc=2, borderaxespad=0.)
plt.savefig(fname = '/volume1/Database/summary.png', dpi=150, bbox_inches='tight')


df = pd.read_csv('/volume1/Database/ing/journal_list.csv', encoding='utf-8')

df_count = df.groupby(['文章進度', '全文']).count()['流水號'].unstack(fill_value=0).stack().to_frame()
df_count.index.names = ['文章進度(1.初稿完成 2.校稿中 3.定稿)', '全文(1.全翻 0.簡翻)']
df_count.columns = ['數量']
df_count.to_csv('/volume1/Database/summary.csv', encoding='utf-8-sig')


#!/bin/bash
import json  # to handle the json file
import re    # to find all decimal numbers
import sys   # to use sys.argv[i]

# this reads the file that is sent in as a argument -> python seg.py file.ini
json_object = json.load(open('{}'.format(sys.argv[1]),'r'))

# get the status_intervals portion of the file (the portion we care about)
match = json_object['status_intervals']


# take account of all of the times that are bad 
bad = []


# grab the number status that comes after the end_sec: 720
num_status = int(match[0]['num_status'])
# if num_status != 0 there there is a problem
if num_status != 0:
        day = re.findall('\d{2}-\d{2}-\d{2}-\d{2}-\d{2}',sys.argv[1])
        print('Time segments bad for {}'.format(''.join(day)))
	print('fault: {}'.format(num_status))
	
	# this gives the number of lines in the area of interest after \n split
	length = (len(match[0]['txt_status'].split('\n')) - 2)
	# -2 because we don't care about the last two lines GPS range checked and Run by detchar...
	
	# loop throught and grab the issue descriptor and time intervals for each
	for i in range(0,length,2):
		j=i+1 # i = current line j = next line
		# name of the particular issue i.e. "Missing Data"
		issueDescriptor = match[0]['txt_status'].splitlines()[i]
		# list of only the start and end times for the current issue
		issueTime = re.findall('\d+',match[0]['txt_status'].splitlines()[j])
		# print the issue
		print('\nissue: {}'.format(issueDescriptor))
		# print column heading 
		print('beginnig:  \tending:')
		
		# auto sizing forloop to get through current issue start and end times
		for k in range(0,len(issueTime),2):
			m=k+1 # m = starting time j = ending time
			print('{}\t{}'.format(issueTime[k],issueTime[m]))
			# append the times to bad array 
			bad.append(issueTime[k])
	print("Bad List")
        print(bad)
else: # num_status == 0
	day = re.findall('\d{2}-\d{2}-\d{2}-\d{2}-\d{2}',sys.argv[1])
	print('Time segments good for {}'.format(''.join(day)))
	

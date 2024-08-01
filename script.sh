#!/bin/bash

# Flags
FLAG_COURSES=false
FLAG_ENROL=false
FLAG_HELP=true
FLAG_UPDATE=false
FLAG_USER=false
FLAG_NOVEDADES=false
# Limit
limit=""
# Parse command-line arguments
while getopts ":l:acehunU" opt; do
  case $opt in
    a)
      FLAG_COURSES=true
      FLAG_ENROL=true
      FLAG_UPDATE=false
      FLAG_USER=true
      FLAG_HELP=false
      FLAG_NOVEDADES=true
      ;;
    c)
      FLAG_COURSES=true
      FLAG_HELP=false
      ;;
    e)
      FLAG_ENROL=true
      FLAG_HELP=false
      ;;
    h)
      FLAG_HELP=true
      ;;
    U)
      FLAG_UPDATE=true
      FLAG_HELP=false
      ;;
    u)
      FLAG_USER=true
      FLAG_HELP=false
      ;;
    l)
      limit=$OPTARG
      ;;
    n)
      FLAG_NOVEDADES=true
      FLAG_HELP=false
      ;;
    \?)
      echo "Invalid option: -$OPTARG
      Try -h or --help to get help." >&2
      exit 1
      ;;
    :)
      echo "Option -$OPTARG requires an argument." >&2
      exit 1
      ;;
  esac
done





if [[ "$FLAG_HELP" == true ]]; then
  echo "########################################Script help#################################################
#############################Script help############################################################
##                                                                                                ##
##  Default values                                                                                ##
##  arg1  flag  = -h                                                                              ##
##  arg2  limit = 1                                                                               ##
##                                                                                                ##
##  Flags           Description                                                                   ##
##                                                                                                ##
##  -a              Executes all the producers and Consumers except the courses updater           ##
##  -c              Executes the complementary and titled producers and consumers                 ##
##  -e              Executes the complementary and titled courses enrol producer and consumer     ##
##  -h              Shows the help message                                                        ##
##  -U              Executes the Courses updater producer and consumer                            ##
##  -u              Executes the users producer and consumer.                                     ##
##  -n              Executes all newness for enrolment and courses.                               ##
##  -l n            To define a new limit for the consumer                                        ##
##                                                                                                ##
########################################Script help#################################################
#############################Script help############################################################"
fi




# Setting a default value to limit
limit=${limit:-1}



# Define the Controller directory
directoryController="./src/Controller"

# Define the Consumer directory
directoryConsumer="./src/Engine/Rabbit"

# Check if the directory exists
if [ ! -d "$directoryController" ]; then
  echo "Controller directory does not exist."
  exit 1
fi
# Check if the directory exist
if [ ! -d "$directoryConsumer" ]; then
  echo "Engine/Rabbit directory does not exist."
  exit 1
fi

controllers=()
trap 'kill "${controllers[@]}"' EXIT# ...

run_consumers() {

  # Loop through all the files in the Rabbit directory
   for file in "$directoryConsumer"/*; do
       # Check if the file is a regular file
     if [ -f "$file" ]; then
       filename=$(basename "$file")
       # Validation to execute the updater file except AllConnection and Queue
       if [[ "$filename" == "ConsumerUpdateFullname.php" && "$FLAG_UPDATE" == true && ${filename:0:1} != "." ]]; then
         for i in $(seq 1 $limit); do
           echo "$file"
           nohup php $file & 
           done
       fi
       # Validation to execute users consumer file except AllConnection and Queue
       if [[ "$filename" == "ConsumerUser.php" && "$FLAG_USER" == true && ${filename:0:1} != "." ]]; then
         for i in $(seq 1 $limit); do
           echo "$file"
           nohup php $file &
           done
       fi
       # Validation to execute courses duplication consumers file except AllConnection and Queue
       if [[( "$filename" == "ConsumerComplementaryDuplicate.php" || "$filename" == "ConsumerTitledDuplicate.php") && "$FLAG_COURSES" == true && ${filename:0:1} != "." ]]; then
         for i in $(seq 1 $limit); do
           echo "$file"
        nohup php $file &
           done
       fi
       # Validation to execute courses duplication consumers file except AllConnection and Queue
       if [[( "$filename" == "ConsumerCEnrolment.php" || "$filename" == "ConsumerTEnrolment.php") && "$FLAG_ENROL" == true && ${filename:0:1} != "." ]]; then
         for i in $(seq 1 $limit); do
           echo "$file"
           nohup php $file &
           done
       fi
       #Validation to execute courses duplication consumers file except AllConnection and Queue
        if [[ "$FLAG_NOVEDADES" == true && ("$filename" == "ConsumerFCnovedad.php" || "$filename" == "ConsumerFPnovedad.php" || "$filename" == "ConsumerFTnovedad.php") &&  ${filename:0:1} != "." ]]; then
          for i in $(seq 1 $limit); do
            echo "$file"
            nohup php $file &
            done
        fi
     fi
done

}




# Loop through all the files in the directory /Controller
for file in "$directoryController"/*; do
  # Check if the file is a regular file
  if [ -f "$file" ]; then
    # Extraction of filename
    filename=$(basename "$file")
    # Validation to only execute updateFullnameControllerQueue
    if [[ "$filename" == "updateFullnameControllerQueue.php" && "$FLAG_UPDATE" == true && ${filename:0:1} != "." ]]; then
        # Printing the file to be execute
        echo "$filename"
        # Executing file on background
        nohup php $file &
        controllers+=($!)
    fi
    # Validation to only execute createUsersController
    if [[ "$filename" == "createusersController.php" && "$FLAG_USER" == true && ${filename:0:1} != "." ]]; then
        # Printing the file to be execute
        echo "$filename"
        # Executing file on background
        nohup php $file &
        controllers+=($!)
    fi
    # Validation to only execute courses duplications
    if [[ ("$filename" == "duplicateComplementaryCoursesController.php" || "$filename" == "duplicateTitledCoursesController.php") && "$FLAG_COURSES" == true && "${filename:0:1}" != "." ]]; then
        # Printing the file to be execute
        echo "$filename"
        # Executing file on background
        nohup php $file &
        controllers+=($!)
    fi
    # Validation to only execute enrols
    if [[ ("$filename" == "enrolCUsersOneController.php" || "$filename" == "enrolUsersOneController.php") && "$FLAG_ENROL" == true && ${filename:0:1} != "." ]]; then
        # Printing the file to be execute
        echo "$filename"
        # Executing file on background
        nohup php $file &
        controllers+=($!)
    fi
    # Validation to only execute newness
     if [[ ("$filename" == "novficcController.php" || "$filename" == "novficpController.php" || "$filename" == "novfictController.php") && "$FLAG_NOVEDADES" == true && ${filename:0:1} != "." ]]; then
          Printing the file to be execute
         echo "$filename"
          Executing file on background
         nohup php $file &
         controllers+=($!)
     fi
  fi
done 

# Wait for all controller processes to finish
wait


# Run the consumer processes
run_consumers


# Wait for all consumer processes to finish
wait

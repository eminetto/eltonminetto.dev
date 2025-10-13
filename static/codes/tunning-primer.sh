#!/usr/bin/env bash

#########################################################################
#                                                                       #
#       MySQL performance tuning primer script                          #
#       Writen by: Matthew Montgomery <mmontgom@rackspace.com>          #
#       Inspired by: MySQLARd (http://gert.sos.be/demo/mysqlar/)        #
#       Version 1.2-r6                                                  #
#       Licenced under GPLv2                                            #
#                                                                       #
#########################################################################
#########################################################################
#                                                                       #
# Set this socket variable if you have multiple instances running or if # 
# we are unable to find your socket otherwise                           #
#                                                                       #
#########################################################################

socket=

function cecho ()

## -- Function to easliy print colored text -- ##

                                # Color-echo.
                                # Argument $1 = message
                                # Argument $2 = color
{
export black='\E[0m\c'
export boldblack='\E[1;0m\c'
export red='\E[31m\c'
export boldred='\E[1;31m\c'
export green='\E[32m\c'
export boldgreen='\E[1;32m\c'
export yellow='\E[33m\c'
export boldyellow='\E[1;33m\c'
export blue='\E[34m\c'
export boldblue='\E[1;34m\c'
export magenta='\E[35m\c'
export boldmagenta='\E[1;35m\c'
export cyan='\E[36m\c'
export boldcyan='\E[1;36m\c'
export white='\E[37m\c'
export boldwhite='\E[1;37m\c'

local default_msg="No message passed."
                                # Doesn't really need to be a local variable.

message=${1:-$default_msg}      # Defaults to default message.
color=${2:-$black}              # Defaults to black, if not specified.

  echo -e "$color"
  echo -e "$message"
  tput sgr0                     # Reset to normal.
  echo -e "$black"
  return
} 

function print_banner () {

## -- Banner -- ##

cecho "\t\c " $black
cecho "-- MYSQL PERFORMANCE TUNING PRIMER --" $boldblue
cecho "\t     - By: Matthew Montgomery -" $black

}

## -- Find the location of the mysql.sock file -- ##

function check_for_socket () {
        if [ -z "$socket" ] ; then
                if [ -S /var/lib/mysql/mysql.sock ] ; then
                        socket=/var/lib/mysql/mysql.sock
                elif [ -S /tmp/mysql.sock ] ; then
                        socket=/tmp/mysql.sock
                else
                        ps_socket=`netstat -ln | egrep "mysql(d)?\.sock" | awk '{ print $9 }'`
                        if [ "$ps_socket" ] ; then
                        socket=$ps_socket
                        fi
                fi
        fi
        if [ -S "$socket" ] ; then
                echo UP > /dev/null
        else
                cecho "No valid socket file "$socket" found!" $boldred
                cecho "mysqld is not running or it is installed in a custom location" $red
                cecho "Please set the $socket variable at the top of this script."
                exit 1
        fi
}


function check_for_plesk_passwords () {

## -- Check for the existance of plesk and login using it's credentials -- ##

        if [ -f /etc/psa/.psa.shadow ] ; then
                mysql="mysql -S $socket -u admin -p`cat /etc/psa/.psa.shadow`"
                mysqladmin="mysqladmin -S $socket -u admin -p`cat /etc/psa/.psa.shadow`"
        else
                mysql="mysql -S $socket"
                mysqladmin="mysqladmin -S $socket"
        fi
}

function check_mysql_login () {

## -- Test for running mysql -- ##

        is_up=`$mysqladmin ping 2>&1`
        if [ "$is_up" = "mysqld is alive" ] ; then
                echo UP > /dev/null
        elif [ "$is_up" != "mysqld is alive" ] ; then
                cecho "\n\c"
                cecho "- INITIAL LOGIN ATTEMPT FAILED -\n" $boldred
                if [ -z $prompted ] ; then
                find_webmin_passwords
                else
                        return 1
                fi

        else 
                cecho "Unknow exit status" $red
                exit -1
        fi
}

function final_login_attempt () {
        is_up=`$mysqladmin ping 2>&1`
        if [ "$is_up" = "mysqld is alive" ] ; then
                echo UP > /dev/null
        elif [ "$is_up" != "mysqld is alive" ] ; then
                cecho "- FINAL LOGIN ATTEMPT FAILED -\n" $boldred
                exit 1
        fi
}

function second_login_failed () {

## -- create a ~/.my.cnf and exit when all else fails -- ##

        cecho "- RETRY LOGIN ATTEMPT FAILED -\n" $boldred
        cecho "Could not auto detect login info!\n"
        read -p "Do you have your login handy ? [y/N] : "
        case $REPLY in 
                yes | y | Y | YES)
                answer1='yes'
                read -p "User: " user
                read -rsp "Password: " pass
                export mysql="$mysql -u$user -p$pass"
                export mysqladmin="$mysqladmin -u$user -p$pass"
                ;;
                *)
                cecho "\nPlease create a valid login to MySQL"
                cecho "Or, set correct values for  'user=' and 'password=' in ~/.my.cnf"
                ;;
        esac
        cecho "\n\c"
        read -p "Would you like me to create a ~/.my.cnf file for you? [y/N] : "
        case $REPLY in
                yes | y | Y | YES)
                answer2='yes'
                if [ ! -f ~/.my.cnf ] ; then
                        umask 077
                        echo -e "[client]\nuser=$user\npassword=$pass" > ~/.my.cnf
                        if [ "$answer1" != 'yes' ] ; then
                                exit 1
                        else
                                final_login_attempt
                                return 0
                        fi
                else
                        cecho "\n~/.my.cnf already exists!\n" $boldred
                        read -p "Replace ? [y/N] : "
                        if [ "$REPLY" = 'y' ] || [ "$REPLY = 'Y' " ] ; then 
                        echo -e "[client]\nuser=$user\npassword=$pass" > ~/.my.cnf
                                if [ "$answer1" != 'yes' ] ; then
                                        exit 1
                                else
                                        final_login_attempt
                                        return 0
                                fi
                        else
                                cecho "Please set the 'user=' and 'password=' values in ~/.my.cnf"
                                exit 1
                        fi
                fi
                ;;
                *)
                if [ "$answer1" != 'yes' ] ; then
                        exit 1
                else
                        final_login_attempt
                        return 0
                fi
                ;;
        esac
}

function find_webmin_passwords () {

## -- populate the .my.cnf file using values harvested from Webmin -- ##

        cecho "Testing Stored for passwords:\c"
        if [ -f /etc/webmin/mysql/config ] ; then
                user=`grep ^login= /etc/webmin/mysql/config | cut -d "=" -f 2`
                pass=`grep ^pass= /etc/webmin/mysql/config | cut -d "=" -f 2`
                if [  $user ] && [ $pass ] && [ ! -f ~/.my.cnf  ] ; then
                        cecho "Setting login info as User: $user Password: $pass"
                        touch ~/.my.cnf
                        chmod 600 ~/.my.cnf
                        echo -e "[client]\nuser=$user\npassword=$pass" > ~/.my.cnf 
                        cecho "Retrying login"
                        is_up=`$mysqladmin ping 2>&1`
                        if [ "$is_up" = "mysqld is alive"  ] ; then
                                echo UP > /dev/null
                        else
                                second_login_failed
                        fi
                echo
                else
                        second_login_failed
                echo
                fi
        else
        cecho " None Found\n" $boldred
                second_login_failed
        fi
}

#########################################################################
#                                                                       #
#  Function to pull MySQL status variable                               #
#                                                                       #
#  Call using :                                                         #
#       mysql_status \'Mysql_status_variable\' bash_dest_variable       #
#                                                                       #
#########################################################################

function mysql_status () {
        local status=`$mysql -Bse "show /*!50000 global */ status like $1" | 
                awk '{ print $2 }'`
        export "$2"=$status
}

#########################################################################
#                                                                       #
#  Function to pull MySQL server runtime variable                       #
#                                                                       #
#  Call using :                                                         #
#       mysql_variable \'Mysql_server_variable\' bash_dest_variable     #
#       - OR -                                                          #
#       mysql_variableTSV \'Mysql_server_variable\' bash_dest_variable  #
#                                                                       #
#########################################################################

function mysql_variable () {
        local variable=`$mysql -e "show /*!500000 global */ variables like $1" | 
                grep -v Variable_name | awk '{ print $2 }'`
        export "$2"=$variable
}
function mysql_variableTSV () {
        local variable=`$mysql -e "show variables like $1" | 
                grep -v Variable_name | awk -F \t '{ print $2 }'`
        export "$2"=$variable
}

function divide () {

# -- Divide two intigers -- #

        usage="$0 dividend divisor '$variable' scale"
        if [ $1 -ge 1 ] ; then
                dividend=$1
        else
                cecho "Invalid Dividend" $red
                echo $usage
                exit 1
        fi
        if [ $2 -ge 1 ] ; then
                divisor=$2
        else
                cecho "Invalid Divisor" $red
                echo $usage
                exit 1
        fi
        if [ ! -n $3 ] ; then
                cecho "Invalid variable name" $red
                echo $usage
                exit 1
        fi
        if [ -z $4 ] ; then
                scale=2
        elif [ $4 -ge 0 ] ; then
                scale=$4
        else
                cecho "Invalid scale" $red
                echo $usage
                exit 1
        fi
        export $3=$(echo "scale=$scale; $dividend / $divisor" | bc -l)
}

function human_readable () {

#########################################################################
#                                                                       #
#  Convert a value in to human readable size and populate a variable    #
#  with the result.                                                     #
#                                                                       #
#  Call using:                                                          #
#       human_readable $value 'variable name'                           #
#                                                                       #
#########################################################################

        ## value=$1
        ## variable=$2
        scale=$3

        if [ $1 -gt 1048576 ] ; then
                if [ -z $3 ] ; then 
                        scale=0
                fi
                divide $1 1048576 "$2" $scale
                unit="M"
        elif [ $1 -gt 1024 ] ; then
                if [ -z $3 ] ; then
                        scale=2
                fi
                divide $1 1024 "$2" $scale
                unit="K"
        else
                export "$2"=$1
                unit="bytes"
        fi
        # let "$2"=$HR
}

function human_readable_time () {

########################################################################
#                                                                      #
#       Function to produce human readable time                        #
#                                                                      #
########################################################################

        usage="$0 seconds 'variable'"
        if [ -z $1 ] || [ -z $2 ] ; then
                cecho $usage $red
                exit 1
        fi
        days=$(echo "scale=0 ; $1 / 86400" | bc -l)
        remainder=$(echo "scale=0 ; $1 % 86400" | bc -l)
        hours=$(echo "scale=0 ; $remainder / 3600" | bc -l)
        remainder=$(echo "scale=0 ; $remainder % 3600" | bc -l)
        minutes=$(echo "scale=0 ; $remainder / 60" | bc -l)
        seconds=$(echo "scale=0 ; $remainder % 60" | bc -l)
        export $2="$days days $hours hrs $minutes min $seconds sec"
}

function check_mysql_version () {

## -- Print Version Info -- ##

        mysql_variable \'version\' mysql_version
        mysql_variable \'version_compile_machine\' mysql_version_compile_machine

        cecho "MySQL Version $mysql_version $mysql_version_compile_machine"
}

function post_uptime_warning () {

#########################################################################
#                                                                       #
#  Present a reminder that mysql must run for a couple of days to       #
#  build up good numbers in server status variables before these tuning #
#  suggestions should be used.                                          #
#                                                                       #
#########################################################################

        mysql_status \'Uptime\' uptime
        mysql_status \'Threads_connected\' threads
        let queries_per_sec=$questions/$uptime
        human_readable_time $uptime uptimeHR

        cecho "Uptime = $uptimeHR"
        cecho "Avg. qps = $queries_per_sec"
        cecho "Total Questions = $questions"
        cecho "Threads Connected = $threads"
        echo

        if [ $uptime -gt 172800 ] ; then
                cecho "Server has been running for over 48hrs."
                cecho "It should be safe to follow these recommendations"
        else
                cecho "Warning: \c" $boldred
                cecho "Server has not been running for at least 48hrs." $boldred
                cecho "It may not be safe to use these recommendations" $boldred

        fi
        echo ""
        cecho "To find out more information on how each of these" $red
        cecho "runtime variables effects performance visit:" $red
        if [ $major_version == '3.23' ] || [ $major_version == '4.0' ] || [ $major_version == '4.1' ]; then
        cecho "http://dev.mysql.com/doc/refman/4.1/en/server-system-variables.html" $boldblue
        elif [ $major_version == '5.0' ] || [ $major_version == '5.1' ] ; then
        cecho "http://dev.mysql.com/doc/refman/$major_version/en/server-system-variables.html" $boldblue
        else
        echo "UNSUPPORTED MYSQL VERSION"
        exit 1
        fi
}

function check_slow_queries () {

## -- Slow Queries -- ## 

        cecho "SLOW QUERIES" $boldblue

        mysql_status \'Slow_queries\' slow_queries
        mysql_variable \'long_query_time\' long_query_time
        mysql_variable \'log%queries\' log_slow_queries
        prefered_query_time=5
        if [ -e /etc/my.cnf ] ; then
                if [ -z $log_slow_queries ] ; then
                        log_slow_queries=`grep log-slow-queries /etc/my.cnf`
                fi
        fi
        cecho "Current long_query_time = $long_query_time sec."
        cecho "You have \c"
        cecho "$slow_queries \c" $boldred 
        cecho "out of \c"
        cecho "$questions \c" $boldred
        cecho "that take longer than $long_query_time sec. to complete"

        if [ "$log_slow_queries" = 'ON' ] ; then
                cecho "The slow query log is enabled."
        elif [ "$log_slow_queries" = 'OFF' ] ; then
                cecho "The slow query log is \c"
                cecho "NOT \c" $boldred
                cecho "enabled."
        elif [ -z $log_slow_queries ] ; then
                cecho "The slow query log is \c"
                cecho "NOT \c" $boldred
                cecho "enabled."
        else
                cecho "Error: $log_slow_queries" $boldred
        fi

        if [ $long_query_time -gt $prefered_query_time ] ; then
                cecho "Your long_query_time may be too high, I typically set this under $prefered_query_time sec." $red
        else
                cecho "Your long_query_time seems to be fine" $green
        fi 
}

function check_used_connections () {

## -- Used Connections -- ##

        mysql_variable \'max_connections\' max_connections
        mysql_status \'Max_used_connections\' max_used_connections
        mysql_status \'Threads_connected\' threads_connected

        let connections_ratio=$max_used_connections*100/$max_connections

        cecho "MAX CONNECTIONS" $boldblue
        cecho "Current max_connections = $max_connections"
        cecho "Current threads_connected = $threads_connected"
        cecho "Historic max_used_connections = $max_used_connections"
        cecho "The number of used connections is \c"
        if [ $connections_ratio -ge 85 ] ; then
                txt_color=$red
        else 
                txt_color=$green
        fi
        # cecho "$max_used_connections \c" $txt_color
        # cecho "which is \c"
        cecho "$connections_ratio% \c" $txt_color
        cecho "of the configured maximum."
        unset txt_color

        if [ $connections_ratio -ge 85 ] ; then
                cecho "You should raise max_connections" $red
        else 
                cecho "Your max_connections variable seems to be fine." $green
        fi
}

function check_threads() {

## -- Worker Threads -- ##

        cecho "WORKER THREADS" $boldblue

        mysql_status \'Threads_created\' threads_created1
        sleep 1
        mysql_status \'Threads_created\' threads_created2

        mysql_status \'Threads_cached\' threads_cached
        mysql_status \'Uptime\' uptime
        mysql_variable \'thread_cache_size\' thread_cache_size

        let historic_threads_per_sec=$threads_created1/$uptime
        let current_threads_per_sec=$threads_created2-$threads_created1;

        cecho "Current thread_cache_size = $thread_cache_size"
        cecho "Current threads_cached = $threads_cached"
        cecho "Current threads_per_sec = $current_threads_per_sec"
        cecho "Historic threads_per_sec = $historic_threads_per_sec"

        if [ $historic_threads_per_sec -ge 2 ] && [ $threads_cached -le 1 ] ; then
                cecho "Threads created per/sec are overrunning threads cached" $red
                cecho "You should raise thread_cache_size" $red
        elif [ $current_threads_per_sec -ge 2 ] ; then
                cecho "Threads created per/sec are overrunning threads cached" $red
                cecho "You should raise thread_cache_size" $red
        else
                cecho "Your thread_cache_size is fine" $green
        fi
}

function check_key_buffer_size () {

## -- Key buffer Size -- ##

        cecho "KEY BUFFER" $boldblue

        mysql_status \'Key_read_requests\' key_read_requests
        mysql_status \'Key_reads\' key_reads
        mysql_status \'Key_blocks_used\' key_blocks_used
        mysql_status \'Key_blocks_unused\' key_blocks_unused
        mysql_variable \'key_buffer_size\' key_buffer_size
        mysql_variable \'datadir\' datadir

        
        myisam_indexes=`$mysql -Bse "/*!50000 SELECT SUM(INDEX_LENGTH) from information_schema.TABLES where ENGINE='MyISAM' 
*/"`
        OS=$(uname)

        if [ "$OS" == 'Darwin' ] || [ "$OS" == 'FreeBSD' ] || [ "$OS" == 'OpenBSD' ] ; then
                duflags=
        else
                duflags='-b'
        fi
        if [ -z "$myisam_indexes" ] ; then
                myisam_indexes=`find $datadir -name '*.MYI' -exec du $duflags '{}' \; | awk '{ s += $1 } END { 
printf("%i\n", s )}'`
        fi

        if [ $key_reads -eq 0 ] ; then
                cecho "No key reads?!" $boldred
                cecho "Seriously look into using some indexes" $red
                key_cache_miss_rate=0
                key_buffer_ratio=0
                key_buffer_ratioRND=0
        else
                let key_cache_miss_rate=$key_read_requests/$key_reads
                if [ ! -z $key_blocks_unused ] ; then
                        let key_blocks_total=$key_blocks_used+$key_blocks_unused
                        divide $key_blocks_used $key_blocks_total key_buffer_fill 2
                        key_buffer_ratio=$(echo "$key_buffer_fill * 100" | bc -l)
                        key_buffer_ratioRND=$(echo "scale=0; $key_buffer_ratio / 1" | bc -l)
                else
                        key_buffer_ratio='Unknown'
                        key_buffer_ratioRND=75
                fi
        fi

        human_readable $myisam_indexes myisam_indexes_HR 0
        cecho "Current MyISAM index space = $myisam_indexes_HR $unit" 

        human_readable  $key_buffer_size key_buffer_size_HR 0
        cecho "Current key_buffer_size = $key_buffer_size_HR $unit"
        cecho "Key cache miss rate is 1 / $key_cache_miss_rate"
        cecho "Key buffer fill ratio = $key_buffer_ratio %" 

        if [ $key_cache_miss_rate -le 100 ] && [ $key_cache_miss_rate -gt 0 ] && [ $key_buffer_ratioRND -ge 80 ]; then
                cecho "You could increase key_buffer_size" $boldred
                cecho "It is safe to raise this up to 1/4 of total system memory;"
                cecho "assuming this is a dedicated database server."
        elif [ $key_buffer_ratioRND -ge 80 ] && [ $key_buffer_size -le $myisam_indexes ] ; then
                cecho "You could increase key_buffer_size" $boldred
                cecho "It is safe to raise this up to 1/4 of total system memory;"
                cecho "assuming this is a dedicated database server."
        elif [ $key_cache_miss_rate -ge 10000 ] || [ $key_buffer_ratioRND -le 50  ] ; then
                cecho "Your key_buffer_size seems to be too high." $red 
                cecho "Perhaps you can use these resources elsewhere" $red
        else
                cecho "Your key_buffer_size seems to be fine" $green
        fi
}

function check_query_cache () {

## -- Query Cache -- ##

        cecho "QUERY CACHE" $boldblue

        mysql_variable \'version\' mysql_version
        mysql_variable \'query_cache_size\' query_cache_size
        mysql_status \'Qcache_free_memory\' qcache_free_memory
        mysql_status \'Qcache_lowmem_prunes\' qcache_lowmem_prunes

        if [ -z $query_cache_size ] ; then
                cecho "You are using MySQL $mysql_version, no query cache is supported. \nI recommend an upgrade to MySQL 
4.0 or better" $red
        elif [ $query_cache_size -eq 0 ] ; then
                cecho "Query cache is supported but not enabled" $red
                cecho "Perhaps you should set the query_cache_size" $red
        else
                let qcache_used_memory=$query_cache_size-$qcache_free_memory
                qcache_fill_ratio=$(echo "scale=2; $qcache_used_memory * 100 / $query_cache_size" | bc -l)
                qcache_fill_ratio_HR=$(echo "scale=0; $qcache_fill_ratio / 1" | bc -l)
                cecho "Query cache is enabled" $green
                human_readable $query_cache_size query_cache_size_HR
                cecho "Current query_cache_size = $query_cache_size_HR $unit"
                human_readable $qcache_used_memory qcache_used_memory_HR
                cecho "Current query_cache_used = $qcache_used_memory_HR $unit"
                cecho "Current Query cache fill ratio = $qcache_fill_ratio %"
                if [ $qcache_fill_ratio_HR -le 25 ] ; then
                        cecho "Your query_cache_size seems to be too high." $red
                        cecho "Perhaps you can use these resources elsewhere" $red
                fi
                if [ $qcache_lowmem_prunes -ge 50 ] && [ $qcache_fill_ratio_HR -ge 80 ]; then
                        cecho "However, \c"
                        cecho "$qcache_lowmem_prunes \c" $boldred
                        cecho "queries have been removed from the query cache due to lack of memory"
                        cecho "Perhaps you should raise query_cache_size" $boldred
                fi
        fi

}

function check_sort_operations () {

## -- Sort Operations -- ##

        cecho "SORT OPERATIONS" $boldblue

        mysql_status \'Sort_merge_passes\' sort_merge_passes
        mysql_status \'Sort_scan\' sort_scan
        mysql_status \'Sort_range\' sort_range
        mysql_variable \'sort_buffer%\' sort_buffer_size 
        mysql_variable \'read_rnd_buffer_size\' read_rnd_buffer_size 

        let total_sorts=$sort_scan+$sort_range
        if [ -z $read_rnd_buffer_size ] ; then
                mysql_variable \'record_buffer\' read_rnd_buffer_size
        fi

        ## Correct rounding error in mysqld where 512K != 524288 ##
        let sort_buffer_size=$sort_buffer_size+8
        let read_rnd_buffer_size=$read_rnd_buffer_size+8

        human_readable $sort_buffer_size sort_buffer_size_HR
        cecho "Current sort_buffer_size = $sort_buffer_size_HR $unit"

        human_readable $read_rnd_buffer_size read_rnd_buffer_size_HR
        cecho "Current record/read_rnd_buffer_size = $read_rnd_buffer_size_HR $unit"

        if [ $total_sorts -eq 0 ] ; then 
                cecho "No sort operations have been performed"
                passes_per_sort=0
        fi
        if [ $sort_merge_passes -ne 0 ] ; then
                let passes_per_sort=$sort_merge_passes/$total_sorts
        else
                passes_per_sort=0
        fi

        if [ $passes_per_sort -ge 2 ] ; then
                cecho "On average \c"
                cecho "$passes_per_sort \c" $boldred
                cecho "sort merge passes are made per sort operation"
                cecho "You should raise your sort_buffer_size"
                cecho "You should also raise your \c"
                if [ $major_version == '3.23' ] ; then 
                        cecho "record_rnd_buffer_size"
                else
                        cecho "read_rnd_buffer_size"
                fi
        else
                cecho "Sort buffer seems to be fine" $green
        fi
}

function check_join_operations () {

## -- Joins -- ##

        cecho "JOINS" $boldblue

        mysql_status \'Select_full_join\' select_full_join
        mysql_status \'Select_range_check\' select_range_check
        mysql_variable \'join_buffer%\' join_buffer_size

        human_readable $join_buffer_size join_buffer_size_HR

        cecho "Current join_buffer_size = $join_buffer_size_HR $unit"
        cecho "You have had $select_full_join queries where a join could not use an index properly"

        if [ $select_range_check -eq 0 ] && [ $select_full_join -eq 0 ] ; then
                cecho "Your joins seem to be using indexes properly" $green
        fi
        if [ $select_full_join -gt 0 ] ; then
                print_error='true'
        fi
        if [ $select_range_check -gt 0 ] ; then
                cecho "You have had $select_range_check joins without keys that check for key usage after each row" $red
                print_error='true'
        fi
        ## Debuging ##
        # print_error='true'
        if [ $print_error ] ; then 
                if [ $major_version == '3.23' ] || [ $major_version == '4.0' ] ; then
                        cecho "You should enable \"log-long-format\" "
                elif [ $major_version == '4.1' ] || [ $major_version == '5.0' ] || [ $major_version == '5.1' ] ; then
                        cecho "You should enable \"log-queries-not-using-indexes\""
                fi
                cecho "Then look for non indexed joins in the slow query log."
                cecho "If you are unable to optimize your queries you may want to increase your"
                cecho "join_buffer_size to accommodate larger joins in one pass."
        fi

        # XXX Add test for join_buffer_size 
}

check_tmp_tables () {

## -- Temp Tables -- ##

        cecho "TEMP TABLES" $boldblue

        mysql_status \'Created_tmp_tables\' created_tmp_tables 
        mysql_status \'Created_tmp_disk_tables\' created_tmp_disk_tables
        mysql_variable \'tmp_table_size\' tmp_table_size

        human_readable $tmp_table_size tmp_table_size_HR 

        if [ $created_tmp_tables -eq 0 ] ; then
                tmp_disk_tables=0
        else
                let tmp_disk_tables=created_tmp_disk_tables*100/created_tmp_tables
        fi
        cecho "Current tmp_table_size = $tmp_table_size_HR $unit"
        cecho "$tmp_disk_tables% of tmp tables created were disk based"
        if [ $tmp_disk_tables -ge 25 ] ; then
                cecho "Perhaps you should increase your tmp_table_size" $red
        else
                cecho "Created disk tmp tables ratio seems fine" $green
        fi
}

function check_table_cache () {

## -- Table Cache -- ##

        cecho "TABLE CACHE" $boldblue

        mysql_variable \'datadir\' datadir
        mysql_variable \'table_cache\' table_cache

        ## /* MySQL +5.1 version of table_cache */ ## 
        mysql_variable \'table_open_cache\' table_open_cache
        mysql_variable \'table_definition_cache\' table_definition_cache

        mysql_status \'Open_tables\' open_tables
        mysql_status \'Opened_tables\' opened_tables
        mysql_status \'Open_table_definitions\' open_table_definitions
 
#       socket_owner=`find $socket -printf '%u\n'`
        socket_owner=`ls -l $socket | awk '{ print $3 }'`
        script_runner=`whoami`

        table_count=`$mysql -Bse "/*!50000 SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE='BASE TABLE' 
*/"`

        if [ -z $table_count ] ; then
                if [ "$script_runner" != "$socket_owner" ] && [ "$script_runner" != "root" ] ; then
                        cecho "You are not '$socket_owner' or 'root'" $red
                        cecho "I am unable to determine the table_count!" $red
                else
                        table_count=`find $datadir 2>&1 | grep -c .frm$`
                fi
        fi
        if [ $table_open_cache ] ; then
                table_cache=$table_open_cache
        fi

        if [ $opened_tables -ne 0 ] && [ $table_cache -ne 0 ] ; then 
                let table_cache_hit_rate=$open_tables*100/$opened_tables
                let table_cache_fill=$open_tables*100/$table_cache
        elif [ $opened_tables -eq 0 ] && [ $table_cache -ne 0 ] ; then
                table_cache_hit_rate=100
                let table_cache_fill=$open_tables*100/$table_cache
        else
                cecho "ERROR no table_cache ?!" $boldred
                exit 1
        fi
        if [ $table_cache ] && [ ! $table_open_cache ] ; then
                cecho "Current table_cache value = $table_cache tables"
        fi
        if [ $table_open_cache ] ; then
                cecho "Current table_open_cache = $table_open_cache tables"
                cecho "Current table_definition_cache = $table_definition_cache tables"
        fi
        if [ $table_count ] ; then
        cecho "You have a total of $table_count tables"
        fi

        if  [ $table_cache_fill -lt 95 ] ; then
                cecho "You have \c"
                cecho "$open_tables \c" $green
                cecho "open tables." 
                cecho "The table_cache value seems to be fine" $green
        elif [ $table_cache_hit_rate -le 85 -o  $table_cache_fill -ge 95 ]; then
                cecho "You have \c"
                cecho "$open_tables \c" $boldred
                cecho "open tables."
                cecho "Current table_cache hit rate is \c" 
                cecho "$table_cache_hit_rate%\c" $boldred
                cecho ", while \c"
                cecho "$table_cache_fill% \c" $boldred
                cecho "of your table cache is in use"
                cecho "You should probably increase your table_cache" $red
        else
                cecho "Current table_cache hit rate is \c"
                cecho "$table_cache_hit_rate%\c" $green
                cecho ", while \c"
                cecho "$table_cache_fill% \c" $green
                cecho "of your table cache is in use"
                cecho "The table cache value seems to be fine" $green
        fi
        if [ $table_definition_cache ] && [ $table_definition_cache -le $table_count ] && [ $table_count -ge 100 ] ; then
                cecho "You should probably increase your table_definition_cache value." $red
        fi
}

function check_table_locking () {

## -- Table Locking -- ##

        cecho "TABLE LOCKING" $boldblue

        mysql_status \'Table_locks_waited\' table_locks_waited
        mysql_status \'Table_locks_immediate\' table_locks_immediate
        mysql_variable \'concurrent_insert\' concurrent_insert
        mysql_variable \'low_priority_updates\' low_priority_updates
        if [ "$concurrent_insert" = 'ON' ]; then
                let concurrent_insert=1
        elif [ "$concurrent_insert" = 'OFF' ]; then
                let concurrent_insert=0
        fi

        cecho "Current Lock Wait ratio = \c"
        if [ $table_locks_waited -gt 0 ]; then
                let immediate_locks_miss_rate=$table_locks_immediate/$table_locks_waited
                cecho "1 : $immediate_locks_miss_rate" $red 
        else
                let immediate_locks_miss_rate=99999 # perfect
                cecho "0 : $questions" $green
        fi
        if [ $immediate_locks_miss_rate -lt 5000 ] ; then
                cecho "You may benefit from selective use of InnoDB."
                if [ "$low_priority_updates" == 'OFF' ] ; then
                cecho "If you have long running SELECT's against MyISAM tables \c"
                cecho "and perform frequent updates consider setting 'low_priority_updates=1'"
                fi
                if [ $concurrent_insert -le 1 ] && [ $major_version == '5.0' ] ; then
                cecho "If you have a high concurrentcy of inserts on Dynamic row-lenght tables \c"
                cecho "consider setting 'concurrent_insert=2'."
                elif  [ $concurrent_insert -le 1 ] && [ $major_version == '5.1' ] ; then
                cecho "If you have a high concurrentcy of inserts on Dynamic row-lenght tables \c"
                cecho "consider setting 'concurrent_insert=2'."
                fi
        else
                cecho "Your table locking seems to be fine" $green
        fi
}

function check_table_scans () {

## -- Table Scans -- ##

        cecho "TABLE SCANS" $boldblue

        mysql_status \'Com_select\' com_select
        mysql_status \'Handler_read_rnd_next\' read_rnd_next
        mysql_variable \'read_buffer_size\' read_buffer_size

        if [ -z $read_buffer_size ] ; then
                mysql_variable \'record_buffer\' read_buffer_size
        fi

        human_readable $read_buffer_size read_buffer_size_HR
        cecho "Current read_buffer_size = $read_buffer_size_HR $unit"

        if [ $com_select -gt 0 ] ; then
                let full_table_scans=$read_rnd_next/$com_select 
                cecho "Current table scan ratio = $full_table_scans : 1"
                if [ $full_table_scans -ge 4000 ] && [ $read_buffer_size -le 2097152 ] ; then
                        cecho "You have a high ratio of sequential access requests to SELECTs" $red
                        cecho "You may benefit from raising \c" $red
                        if [ $major_version == '3.23' ] ; then 
                                cecho "record_buffer \c" $red
                        else
                                cecho "read_buffer_size \c" $red
                        fi
                        cecho "and/or improving your use of indexes." $red
                elif [ $read_buffer_size -gt 2097152 ] ; then 
                        cecho "read_buffer_size is over 2 MB \c" $red 
                        cecho "there is probably no need for such a large read_buffer" $red

                else
                        cecho "read_buffer_size seems to be fine" $green
                fi
        else
                cecho "read_buffer_size seems to be fine" $green
        fi
}


function check_innodb_status () {

## -- InnoDB -- ##

        mysql_variable \'have_innodb\' have_innodb

        if [ "$have_innodb" = "YES" ] ; then
                mysql_variable \'innodb_buffer_pool_size\' innodb_buffer_pool_size
                echo
                cecho "INNODB STATUS" $boldblue
                innodb_indexes=`$mysql -Bse "/*!50000 SELECT SUM(INDEX_LENGTH) from information_schema.TABLES where 
ENGINE='InnoDB' */"`

                if [ ! -z "$innodb_indexes" ] ; then
                human_readable $innodb_indexes innodb_indexes_HR 0
                cecho "Current InnoDB index space = $innodb_indexes_HR $unit"
                else
                cecho "Cannot find InnoDB index space prior to 5.0.x" $red
                fi

                human_readable $innodb_buffer_pool_size innodb_buffer_pool_sizeHR
                cecho "Current innodb_buffer_pool_size = $innodb_buffer_pool_sizeHR $unit"
                cecho "Depending on how much space your innodb indexes take up it may be safe"  
                cecho "to increase this value to up to 1 / 3 of total system memory"
                echo
                $mysql -s -e "SHOW /*!50000 ENGINE */INNODB STATUS\G"
        else
                cecho "No InnoDB Support Enabled!" $boldred
        fi
}

function total_memory_used () {

## -- Total Memory Usage -- ##
        cecho "MEMORY USAGE" $boldblue

        mysql_variable \'read_buffer_size\' read_buffer_size
        mysql_variable \'sort_buffer_size\' sort_buffer_size
        mysql_variable \'thread_stack\' thread_stack
        mysql_variable \'max_connections\' max_connections
        mysql_status \'Max_used_connections\' max_used_connections

        let per_thread_buffers=($read_buffer_size+$sort_buffer_size+$thread_stack)*$max_used_connections
        let per_thread_max_buffers=($read_buffer_size+$sort_buffer_size+$thread_stack)*$max_connections

        mysql_variable \'innodb_buffer_pool_size\' innodb_buffer_pool_size
        if [ -z $innodb_buffer_pool_size ] ; then
        innodb_buffer_pool_size=0
        fi

        mysql_variable \'innodb_additional_mem_pool_size\' innodb_additional_mem_pool_size
        if [ -z $innodb_additional_mem_pool_size ] ; then
        innodb_additional_mem_pool_size=0
        fi

        mysql_variable \'innodb_log_buffer_size\' innodb_log_buffer_size
        if [ -z $innodb_log_buffer_size ] ; then
        innodb_log_buffer_size=0
        fi

        mysql_variable \'key_buffer_size\' key_buffer_size

        mysql_variable \'query_cache_size\' query_cache_size
        if [ -z $query_cache_size ] ; then
        query_cache_size=0
        fi


        let global_buffers=$innodb_buffer_pool_size+$innodb_additional_mem_pool_size+$innodb_log_buffer_size+$key_buffer_size+$query_cache_size

        let total_memory=$global_buffers+$per_thread_buffers
        let max_memory=$global_buffers+$per_thread_max_buffers
        human_readable $total_memory total_memoryHR 0
        cecho "Max Memory Ever Allocated : $total_memoryHR $unit" $boldred
        human_readable $max_memory max_memoryHR 0
        cecho "Configured Max Memory Limit : $max_memoryHR $unit" $boldred

        total_system_memory=`free -b | grep -v buffers |  awk '{ s += $2 } END { printf("%ld\n", s ) }'`
        human_readable $total_system_memory total_system_memoryHR 0
        cecho "Total System Memory : $total_system_memoryHR $unit" $boldred
}

function snarky () {

## -- Be Snarky -- ##

        fortune=`which fortune 2>/dev/null` 
        if [ -z $fortune ] ; then
                echo "What the hell sort of straight-lace bastard doesn't have fortune installed?"
        else
                $fortune
        fi
}

## Required Functions  ## 

function login_validation () {
        check_for_socket                # determine the socket location -- 1st login
        check_for_plesk_passwords       # determine the login method -- 2nd login
        check_mysql_login               # determine if mysql is accepting login -- 3rd login
        export major_version=`$mysql -Bse 'select substring_index(version(), ".", +2)'`
        export OS=`uname`
        mysql_status \'Questions\' questions
}

## Optional Components Groups ##

function banner_info () {
        print_banner            ; echo
        check_mysql_version     ; echo
        post_uptime_warning     ; echo
}

function misc () {
        check_slow_queries      ; echo
        check_used_connections  ; echo
        check_threads           ; echo
}

function memory () {
        total_memory_used       ; echo
        check_key_buffer_size   ; echo
        check_query_cache       ; echo
        check_sort_operations   ; echo
        check_join_operations   ; echo
}

function file () {
        check_table_cache       ; echo
        check_tmp_tables        ; echo
        check_table_scans       ; echo
        check_table_locking     ; echo
}

function all () {
        banner_info
        misc
        memory
        file
#       snarky
}

function prompt () {
        prompted='true'
        read -p "Username [anonymous] : " user
        read -rsp "Password [<none>] : " pass
        cecho "\n\c"
        read -p "Socket [ /var/lib/mysql/mysql.sock ] : " socket
        if [ -z $socket ] ; then
                export socket='/var/lib/mysql/mysql.sock'
        fi
        if [ -n $pass ] ; then
                pass_flag='-p'
        fi
        mysql="mysql -S $socket -u$user $pass_flag$pass"
        mysqladmin="mysqladmin -S $socket -u$user $pass_flag$pass"
        echo $mysql
        check_for_socket
        check_mysql_login
        if [ $? = 1 ] ; then
                exit 1
        fi
        read -p "Mode to test (see usage:) [all] : " pmode
        case $pmode in
                banner )
                banner_info 
                ;;
                misc )
                misc
                ;;
                memory )
                memory
                ;; 
                file )
                file
                ;;
                innodb )
                innodb
                ;;
                all | *)
                all
                ;;
        esac 
}

if [ -z $1 ] ; then
        login_validation
        mode='ALL'
elif [ "$1" != "prompt" ] || [ "$1" != "PROMPT" ] ; then
        login_validation
        mode=$1
elif [ "$1" = "prompt" ] || [ "$1" = "PROMPT" ] ; then
        mode=$1
fi

case $mode in 
        ALL | all )
        cecho "\n\c"
        all
        ;;
        mem | memory |  MEM | MEMORY )
        cecho "\n\c"
        memory
        ;;
        file | FILE | disk | DISK )
        cecho "\n\c"
        file
        ;;
        banner | BANNER | header | HEADER | head)
        banner_info
        ;;
        misc | MISC | miscelaneous )
        cecho "\n\c"
        misc
        ;;
        innodb | INNODB )
        banner_info
        check_innodb_status ; echo
        ;;
        prompt | PROMPT )
        prompt
        ;;
        *)
        cecho "usage: $0 [ all | banner | file | innodb | memory | misc | promp ]" $boldred
        exit 1
        ;;
esac


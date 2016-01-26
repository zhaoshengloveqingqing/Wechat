################################################################################
#
# The make file
#
# @author Jack
# @date Fri Nov  7 11:08:37 2014
# @version 1.0
#
################################################################################


################################################################################
#
# Constants
#
################################################################################

SERVER := 218.244.128.8
LIVE := www.pinet.cc
DB := lingzh

################################################################################
#
# Applications
#
################################################################################

SSH := ssh
ECHO := echo
PHP := php
MYSQL := mysql
AWK := awk

################################################################################
#
# Defines
#
################################################################################

define server_operation
	${SSH} -i id_rsa root@${SERVER} $1;
endef

define live_operation
	${SSH} -i id_rsa pinet@${LIVE} $1;
endef


################################################################################
#
# Tasks
#
################################################################################

deploy: server_pull_code
	@${ECHO} "Done"

live: 
	@${ECHO} Pulling code from git at server ${LIVE}
	@$(call live_operation,"sudo /usr/local/bin/deploy")

server_pull_code:
	@${ECHO} Pulling code from git at server ${SERVER}
	@$(call server_operation,"cd /www && git pull origin development")

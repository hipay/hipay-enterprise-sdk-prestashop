port=$(wget --no-check-certificate --user=$DOCKER_MACHINE_LOGIN --password=$DOCKER_MACHINE_PASS -qO- https://docker-knock-auth.hipay.org/KyP54YzX/?srvname=deploy.hipay-pos-platform.com)

BRANCH=${CIRCLE_BRANCH////-}

echo "Create Artifact project for project $CIRCLE_PROJECT_REPONAME and branch $CIRCLE_BRANCH to /deploy/project/artifactory/$CIRCLE_PROJECT_REPONAME/$BRANCH"
sshpass -p $PASS_DEPLOY ssh root@docker-knock-auth.hipay.org -p $port mkdir /deploy/project/artifactory/$CIRCLE_PROJECT_REPONAME/$BRANCH

echo "Transfert Artifact project for project $CIRCLE_PROJECT_REPONAME and branch $CIRCLE_BRANCH"
sshpass -p $PASS_DEPLOY scp -P $port ./package-ready-for-prestashop/*.zip root@docker-knock-auth.hipay.org:/deploy/project/artifactory/$CIRCLE_PROJECT_REPONAME/$BRANCH

echo "Deploy project in artifactory"
sshpass -p $PASS_DEPLOY ssh root@docker-knock-auth.hipay.org -p $port  "export DOCKER_API_VERSION=1.23 && docker exec " \
    "jira-artifactory-pi.hipay-pos-platform.com" /tmp/jfrog rt u /deploy/project/artifactory/$CIRCLE_PROJECT_REPONAME/$BRANCH/"*.zip" "hipay/org.hipay/" \
    --build-name=Test --build-number=1 --flat=true --user=admin --password=$ARTIFACTORY_PASSWORD --url http://localhost:8081/artifactory

echo "Deploy project for project $CIRCLE_PROJECT_REPONAME and branch $CIRCLE_BRANCH"
sshpass -p $PASS_DEPLOY ssh root@docker-knock-auth.hipay.org -p $port  "export DOCKER_API_VERSION=1.23 && docker exec " \
    "deploy.hipay-pos-platform.com" /deploy/deploy_project.sh  $CIRCLE_PROJECT_REPONAME $CIRCLE_BRANCH

BRANCH=$CI_COMMIT_REF_SLUG

echo "Create Artifact project for project $CI_PROJECT_NAME and branch $GITHUB_BRANCH to /deploy/project/artifactory/$CI_PROJECT_NAME/$BRANCH"
sshpass -p $PASS_DEPLOY ssh root@docker-knock-auth.hipay.org mkdir /deploy/project/artifactory/$CI_PROJECT_NAME/$BRANCH

echo "Transfert Artifact project for project $CI_PROJECT_NAME and branch $GITHUB_BRANCH"
sudo chmod 666 /dev/tty
sshpass -p $PASS_DEPLOY scp ./package-ready-for-prestashop/*.zip root@docker-knock-auth.hipay.org:/deploy/project/artifactory/$CI_PROJECT_NAME/$BRANCH

echo "Deploy project in artifactory"
docker exec jira-artifactory-pi.hipay-pos-platform.com /tmp/jfrog rt u /deploy/project/artifactory/$CI_PROJECT_NAME/$BRANCH/*.zip $CI_PROJECT_NAME/snapshot/ \
    --flat=true --user=admin --password=$ARTIFACTORY_PASSWORD --url http://localhost:8081/artifactory/hipay/


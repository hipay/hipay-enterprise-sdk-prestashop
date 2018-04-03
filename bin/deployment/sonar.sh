
if [ $CI_PULL_REQUESTS != "" ];then
    PR_NUMBER=$(echo $CI_PULL_REQUEST | cut -d'/' -f 7)
    sonar-scanner -Dsonar.host.url=$SONAR_URL -Dsonar.login=$SONAR_LOGIN -Dsonar.analysis.mode=preview -Dsonar.github.pullRequest=$PR_NUMBER -Dsonar.github.oauth=$GITHUB_TOKEN
fi


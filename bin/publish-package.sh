#!/bin/bash -e

setup () {
  eval $(ssh-agent -s) && ssh-add <(echo "${SSH_PRIVATE_KEY}") && mkdir -p ~/.ssh
  echo -e "Host *\n\tStrictHostKeyChecking no\n\n" > ~/.ssh/config
  git config --global user.email ${GIT_EMAIL}
  git config --global user.name ${GIT_NAME}
}

checkout_subtree () {
  echo "pushing ${PACKAGE_VERSION}"

  git remote add ${PACKAGE} ${REPOSITORY} | true

  TEMP_BRANCH=${CI_PIPELINE_ID}_tmp_${PACKAGE_VERSION}

  git branch -D ${TEMP_BRANCH} | true
  git subtree split --prefix=${PACKAGE} -b ${TEMP_BRANCH}
  git checkout ${TEMP_BRANCH}
}

cleanup () {
  git checkout origin/${CI_COMMIT_REF_NAME}
  git branch -D ${TEMP_BRANCH}
}

publish_tag () {
  setup
  checkout_subtree

  git tag -a ${PACKAGE_VERSION} -m "Version ${PACKAGE_VERSION}"
  git push ${PACKAGE} refs/tags/${PACKAGE_VERSION}:refs/tags/${PACKAGE_VERSION}

  cleanup
}

publish_master () {
  setup
  checkout_subtree

  git push ${PACKAGE} ${TEMP_BRANCH}:master

  cleanup
}

case "$1" in
  "master")
    PACKAGE_VERSION="dev-master"
    publish_master
    ;;
  "tag")
    PACKAGE_VERSION=$(cat $PACKAGE/composer.json | grep version | head -1 | awk -F: '{ print $2 }' | sed 's/[",]//g' | tr -d '[[:space:]]')
    publish_tag
    ;;
  *)
    echo "Publish 'tag' or 'master'"
    exit 1
    ;;
esac

#!/bin/bash -e

setup () {
  eval $(ssh-agent -s) && ssh-add <(echo "${SSH_PRIVATE_KEY}") && mkdir -p ~/.ssh
  echo -e "Host *\n\tStrictHostKeyChecking no\n\n" > ~/.ssh/config
  git config --global user.email ${GIT_EMAIL}
  git config --global user.name ${GIT_NAME}
}

checkout_subtree () {
  echo "pushing ${PACKAGE_VERSION}"

  git remote add origin ${REPOSITORY} || true
  git fetch --unshallow origin || true

  git remote add ${PACKAGE} ${REPOSITORY} | true

  TEMP_BRANCH=${CI_PIPELINE_ID}_tmp_${PACKAGE_VERSION}

  git branch -D ${TEMP_BRANCH} | true
  git subtree split --prefix=packages/${PACKAGE} -b ${TEMP_BRANCH}
  git checkout ${TEMP_BRANCH}
}

cleanup () {
  # Delete the tag locally to avoid conflicts
  git tag -d ${PACKAGE_VERSION}
  git checkout origin/${CI_COMMIT_REF_NAME}
  git branch -D ${TEMP_BRANCH}
}

publish_tag () {
  setup
  checkout_subtree

  git tag -a ${PACKAGE_VERSION} -m "Version ${PACKAGE_VERSION}"
  git push -f ${PACKAGE} refs/tags/${PACKAGE_VERSION}:refs/tags/${PACKAGE_VERSION}

  cleanup
}

publish_branch () {
  setup
  checkout_subtree

  git push -f ${PACKAGE} ${TEMP_BRANCH}:${CI_COMMIT_REF_NAME}

  cleanup
}

delete_branch () {
  setup

  git remote add ${PACKAGE} ${REPOSITORY} | true

  git push ${PACKAGE} --delete ${CI_COMMIT_REF_NAME} || true
}

case "$1" in
  "delete-branch")
    delete_branch
    ;;
  "branch")
    PACKAGE_VERSION="dev-${CI_COMMIT_REF_NAME}"
    publish_branch
    ;;
  "tag")
    PACKAGE_VERSION=$(cat packages/$PACKAGE/composer.json | grep version | head -1 | awk -F: '{ print $2 }' | sed 's/[",]//g' | tr -d '[[:space:]]')
    publish_tag
    ;;
  *)
    echo "Publish 'tag' or 'branch'"
    exit 1
    ;;
esac

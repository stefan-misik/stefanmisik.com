#!/usr/bin/env bash

# Title
TITLE=$1
# Published time
PUBLISHED=$(date -u +%Y-%m-%dT%H:%M:%S+0000)
# Directory with posts
POST_HOME=posts/

# Post file name
POSTSDIR=$(dirname $0)/../$POST_HOME

# Format the new post
cd "$POSTSDIR" && \
printf "# %s\n- published: %s\n- tags: \n\n" "$TITLE" "$PUBLISHED" | vim -


# Effective Git Committing with Vim
- published: 2019-03-12T21:01:25+0000
- tags: git, vim

Had it ever happened to you that you were writing git commit message and you
suddenly found your self staring blankly at the blinking cursor being unable to
formulate coherent commit message, due to e.g. not being able to remember all
the changes that you had staged to be committed or the wider context of the
changes being committed? Well, I think I have a solution for you, granted you
are using Vim to write your git commit messages.

My proposed solution is based on mapping few normal-mode key shortcuts to
perform special tasks inside git commit message editing session. These tasks
include:

* Browse the changes being committed
* Browse log of previous commits
* Insert parts of previous commit

```vim
" Obviously we want the spell checking
autocmd FileType gitcommit setlocal spell spelllang=en_us

" F2 will show the changes being committed
autocmd FileType gitcommit nnoremap <F2> :! GIT_PAGER="less -+FX" git diff --cached<CR><CR>

" F3 will show the log of previous commits
autocmd FileType gitcommit nnoremap <F3> :! GIT_PAGER="less -+FX" git log<CR><CR>

" F4 will copy tags from previous commit
autocmd FileType gitcommit nnoremap <F4> :.-1r !git show -s --format="\%s" HEAD \| grep -oE "(\[[a-zA-Z0-9_]+\])+ ?" <CR>
```

First one simply shows `git diff` of the changes being committed, second invokes
`git log`, and the last one is used in my workflow to copy *commit tags* to the
current commit message. Last one is arguably very specific for my workflow,
since I often use tags (short strings enclosed in square brackets at the
beginning of a commit message to identify a subset of the project affected by
the commit - e.g. `[doc]` when commit deals with documentation), however it
serves here as a good demonstration of what can be achieved.

Note that git commands potentially using pager to show their output needed some
special configuration to adapt their behavior to usage from within the Vim.
Since I did not want to be obligated to press another key after finishing the
external  command (normally Vim asks for pressing Enter key after an external
command terminates), I appended these commands with two `<CR><CR>` (first one
initiates the external command and second returns to Vim immediately after the
external command terminates). On the other hand this leads into unusable
behavior in case the pager is not used (by default git commands do not use pager
in case the command's output can fit the screen). In this case no output can be
observed, as the command terminates immediately. This was resolved by prefixing
the external commands with `GIT_PAGER="less -+FX"`, which configures the git
pager to be always used, even in case the output of the command fits the screen.

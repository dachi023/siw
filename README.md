# Siw(Slack-incoming-webhook)
This is notification tool for Slack.

## Futures
- [GitBucket](https://github.com/takezoe/gitbucket)
  - Commit
  - Pull-request
  - Issue

## Installation
- Download latest sources
  - [release page](https://github.com/dachi023/slack-incoming-webhook/releases)

- Clone latest sources
  ```bash
  $ git clone https://github.com/dachi023/slack-incoming-webhook.git
  ```

### Configurataion
Edit the configuration file. When give request parameters, don't need edit it

Please set it in webhook of each service

#### Edit files

```ini
; Bot name
username   = "GitBucket"
; Slack API token
token      = ""
; Notice channel
channel    = ""
; Image url
icon_url   = ""
; Slack emoji
; Priority: icon_emoji > icon_url
icon_emoji = ""
```

#### Parameters
`Priority`: parameters > configuration file settings

- username
- token
- channel
- icon_url
- icon_emoji

### Deploy
#### Heroku
Please install [Heroku Toolbelt](https://toolbelt.heroku.com)

0. When edit the configuration file, please commit it

  ```bash
  $ git add conf/filename.ini
  $ git commit -m "edit configuration files"
  ```

0. Push and deploy

  ```bash
  $ heroku create
  $ git push heroku master
  ```

0. Add service hooks
  - your-domain.herokuapp.com/gitbucket.php

## License
- MIT
  - see LICENSE
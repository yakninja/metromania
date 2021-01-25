# Metromania

Cross-posting your works to multiple publishing sites

## Installation

- `composer install --prefer-dist`
- `php init`
- Edit `common/config/main-local.php`
- Edit `common/config/params-local.php`
- `php yii migrate`
- `php yii rbac/init`
- Connect the Google Docs API: https://developers.google.com/docs/api/quickstart/php
    - Enable the Google Docs API
    - Choose "Desktop App"
    - Download credentials.json and save the file to `common/config`

### Developer?

- `php yii_test migrate`
- `php yii_test rbac/init`
- `codecept run`

### Production?

- Add to cron once per minute: `php yii source-get`

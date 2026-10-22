# OWNewsletter

OWNewsletter is a complete newsletter system for Exponential. Editors write
newsletter editions as ordinary content objects; visitors subscribe to mailing
lists on the site and confirm by email; a cron job builds the mails and sends
them in batches, with optional click and read tracking; bounced mails are read
back from a POP3 or IMAP mailbox.

It is for site owners who want to keep their subscribers, their newsletters and
their archive inside the CMS instead of a third-party mailing service.

- Admin tab: **Newsletter**, drawn with the `envelope` icon in the admin4 design.
- Module: `newsletter` (`/newsletter/index`, `/newsletter/subscribe`, ...).
- License: GNU General Public License v2.0 (see [LICENSE](LICENSE)).

## Features

- Several independent *newsletter systems*, each with its own mailing lists and
  newsletters.
- Mailing lists restricted to chosen siteaccesses, with optional automatic
  approval of subscriptions.
- Subscription, confirmation, self-service configuration and unsubscribe pages,
  with extra subscription form fields defined in `newsletter.ini`.
- Newsletter editions in any content class of the class group
  `Newsletter Editions`, rendered through skins.
- Test sending, sending as soon as possible or from a date, and aborting.
- Queued sending by cron in sessions of a configurable size.
- Mail personalization classes (subject, HTML body and text body per user).
- Click and read tracking markers, with examples for Google Analytics and XiTi.
- Bounce handling from POP3/IMAP mailboxes, with a threshold after which a
  subscriber is marked as bounced.
- Blacklist, subscriber administration, CSV import and export of subscriptions.

## Requirements

- Exponential 6.x (the legacy kernel), with an admin siteaccess.
- PHP 8.0 or later.
- The extension `ezjscore` (the admin uses its server functions) and Zeta
  Components Mail, which the Exponential kernel already brings.
- [OWMigration](https://github.com/se7enxweb/OWMigration) to create the content
  classes (see step 5 below), or the classes created by hand.
- A way to run cron jobs on the server.

## Installation

### With Composer

```bash
composer require se7enxweb/ownewsletter
```

The Exponential legacy installer places the extension in `extension/ownewsletter`.
Then continue with step 2 of the manual installation.

### By hand

1. Copy (or `git clone`) the extension into `extension/ownewsletter`. The
   directory must be called `ownewsletter` in lowercase: the extension's settings
   and scripts refer to `extension/ownewsletter`.

   ```bash
   git clone https://github.com/se7enxweb/OWNewsletter.git extension/ownewsletter
   ```

2. Activate it in `settings/override/site.ini.append.php`:

   ```ini
   [ExtensionSettings]
   ActiveExtensions[]=ownewsletter
   ```

3. Create the database tables (`ownl_user`, `ownl_subscription`,
   `ownl_mailing_list`, `ownl_newsletter`, `ownl_edition`, `ownl_sending`,
   `ownl_mailbox`, `ownl_bounce`, `ownl_blacklist_item`) from the schema file,
   with your database type, user and name:

   ```bash
   php bin/php/ezsqlinsertschema.php --type=mysql --user=<db user> --password=<db password> \
       extension/ownewsletter/share/db_schema.dba <database name>
   ```

4. Regenerate the autoload array and clear the caches:

   ```bash
   php bin/php/ezpgenerateautoloads.php -e
   php bin/php/ezcache.php --clear-all
   ```

5. Create the content classes. With OWMigration installed and active, run the
   extension's migrations (the extension registers itself in
   `[MigrationSettings] MigrationExtensions[]`):

   ```bash
   php extension/owmigration/bin/migrate.php --extension=ownewsletter
   ```

   They create, in the class group `Newsletter`: `newsletter_collection`,
   `newsletter_system`, `newsletter_mailing_list_collection`,
   `newsletter_mailing_list` and `newsletter`; in the class group
   `Newsletter Editions`: `newsletter_edition`. The last migration gives the
   `Anonymous` role the policies `newsletter/subscribe`, `newsletter/configure`
   and `newsletter/unsubscribe`.

6. Create a section for the newsletter content, with the navigation part
   **Newsletter** (`eznewsletternavigationpart`).

7. Set up the cron jobs (see [Cron jobs](#cron-jobs)).

## Content structure

Create this tree in the newsletter section:

```
Newsletters                         [Newsletter collection]
└── Newsletters                     [Newsletter system]
    ├── Mailing list collection 1   [Newsletter mailing list collection]
    │   ├── Mailing list 1          [Newsletter mailing list]
    │   └── Mailing list 2          [Newsletter mailing list]
    ├── Newsletter 1                [Newsletter]
    │   ├── Edition 1               [Newsletter edition]
    │   └── Edition 2               [Newsletter edition]
    └── Newsletter 2                [Newsletter]
        └── Edition 1               [Newsletter edition]
```

- **Newsletter collection**: the root; there must be only one.
- **Newsletter system**: holds mailing lists and newsletters; each system is
  independent of the others.
- **Newsletter mailing list collection**: groups mailing lists, for example one
  collection per site or per language.
- **Newsletter mailing list**: what users subscribe to. Choose the
  siteaccesses users can subscribe from and whether subscriptions are approved
  automatically.
- **Newsletter**: a series of editions. Choose its default mailing lists, the
  siteaccess that renders it, the sender's address and name, the default test
  recipients, its skin and the personalizations to apply.
- **Newsletter edition**: one mail. Choose the mailing lists it goes to.

Set `[NewsletterSettings] RootFolderNodeId` to the node id of the newsletter
collection: the subscription form looks for newsletter systems below it.

### Your own edition classes

You can create as many edition classes as you like, as long as they are in the
class group `Newsletter Editions` (`NewsletterEditionContentClassGroup`) and
contain a `Newsletter Edition` (`ownewsletteredition`) attribute. A mail is
rendered with the template

```
design:newsletter/skin/<skin name>/output/<class identifier>.tpl
```

The extension ships the skin `default` with
`newsletter/skin/default/output/newsletter_edition.tpl`. Add a skin by creating
the same structure in your design and listing it:

```ini
[NewsletterSettings]
AvailableSkinList[]=default
AvailableSkinList[]=corporate
```

## Usage

### Subscribing (site)

| URL                                  | What it does                                   |
|--------------------------------------|------------------------------------------------|
| `/newsletter/subscribe`              | subscription form (`?email=` prefills the address) |
| `/newsletter/confirmation/<hash>`    | confirms the subscription from the mail link   |
| `/newsletter/configure/<hash>`       | lets a subscriber change data and lists        |
| `/newsletter/resendconfigurelink`    | mails the configure link again                 |
| `/newsletter/unsubscribe/<hash>`     | unsubscribes                                   |
| `/newsletter/view/<object id>/<hash>`| the web version of a sent edition              |

The form only shows mailing lists that allow the current siteaccess. A
subscription stays *pending* until it is confirmed; pending users older than
`ConfirmationTimeOut` are removed by a cron job.

### Writing and sending (admin)

1. In the Newsletter tab, open a newsletter and create a **Newsletter edition**
   below it.
2. Open the edition: its full view has a preview tab and a statistics tab, and
   the sending buttons: **Send test newsletter** (to the test addresses),
   **Send newsletter soon as possible**, **Send newsletter from this date** and
   **Abort newsletter**.
3. The cron jobs build the mail queue and send it.

### Administration views

| URL                                        | Function                    |
|--------------------------------------------|-----------------------------|
| `/newsletter/index`                        | newsletter dashboard        |
| `/newsletter/user`                         | subscribers, filtered by `(status)/pending`, `confirmed`, `bounced`, `removed`, `blacklisted` |
| `/newsletter/blacklist`                    | blacklisted addresses       |
| `/newsletter/mailbox`                      | POP3/IMAP mailboxes for bounces |
| `/newsletter/bounce`                       | read and parse bounced mails |
| `/newsletter/subscription_import/<list id>`| import a CSV of subscribers (`;` by default) |
| `/newsletter/subscription_export/<list id>`| export the subscribers of a list |
| `/newsletter/settings`                     | shows `newsletter.ini` for the current siteaccess |

### Template fetch functions and operators

Fetch functions of the module `newsletter`:

| Function                        | Parameters                                                            |
|---------------------------------|-----------------------------------------------------------------------|
| `user_list`, `user_count`       | `mailing_list_contentobject_id`, `user_status`, `subscription_status`, `email` (+ `limit`, `offset` for the list) |
| `subscription_list`, `subscription_count` | `mailing_list_contentobject_id`, `filter_status`, `user_status` (+ `limit`, `offset`) |
| `user_additional_fields`        | none: the `AdditionalFields` of `newsletter.ini`                      |
| `available_subscription_status`, `available_filter_status` | none                                       |
| `filter_status_name`            | `user_status`, `subscription_status`                                  |
| `edition_class_list`, `edition_class_identifer_list`, `edition_class_goup_id` | none (the names are spelled this way in the code) |

User status names: `pending`, `confirmed`, `bounced`, `bounced_soft`,
`bounced_hard`, `removed`, `removed_self`, `removed_admin`. Subscription status
names: `pending`, `approved`, `inactived`.

```smarty
{* Confirmed subscribers of one mailing list *}
{def $users = fetch( 'newsletter', 'user_list', hash(
        'mailing_list_contentobject_id', $node.contentobject_id,
        'user_status', 'confirmed',
        'limit', 20 ) )}
{foreach $users as $user}{$user.email|wash}<br />{/foreach}

{* Pending subscribers in total *}
{fetch( 'newsletter', 'user_count', hash( 'user_status', 'pending' ) )}
```

Template operators:

- `newsletter_edition_content` turns a content object or node into the content
  of its `ownewsletteredition` attribute:
  `{def $edition = $node|newsletter_edition_content()}`.
- `newsletter_str_replace( hash( 'search', 'replace' ) )` replaces strings:
  `{$text|newsletter_str_replace( hash( '[name]', $user.name ) )}`.

Extended attribute filters for `content/list` and `content/tree`:
`newsletter_edition_filter` and `newsletter_mailing_list_filter`, for example
mailing lists open to the current siteaccess:

```smarty
{def $lists = fetch( 'content', 'tree', hash(
        'parent_node_id', ezini( 'NewsletterSettings', 'RootFolderNodeId', 'newsletter.ini' ),
        'class_filter_type', 'include',
        'class_filter_array', array( 'newsletter_mailing_list' ),
        'extended_attribute_filter', hash(
            'id', 'newsletter_mailing_list_filter',
            'params', hash( 'siteaccess', array( 'current_siteaccess' ) ) ) ) )}
```

## Configuration

Settings live in `newsletter.ini`. Override them in
`settings/override/newsletter.ini.append.php` (or per siteaccess); the file in
the extension documents every value. The main ones:

```ini
[NewsletterSettings]
# Node id below which the newsletter systems are looked for
RootFolderNodeId=1
# PHP binary used to render editions (bin/php/createoutput.php)
PhpCli=php
AvailableSkinList[]=default
# Lifetime of a pending subscription, in seconds; a product of numbers is allowed
ConfirmationTimeOut=6*24*60*60
NewsletterEditionContentClassGroup=Newsletter Editions
# enabled: the queue only takes addresses of the domains below
DevelopmentMode=disabled
DevelopmentAuthorizedDomainName[]
#DevelopmentAuthorizedDomainName[]=example.com

[NewsletterMailSettings]
EmailsBySendingSession=500
DelayBeforeNextSendingSession=30
# smtp, sendmail or file, for queued newsletters, test mails and direct mails
TransportMethodCronjob=file
TransportMethodPreview=sendmail
TransportMethodDirectly=sendmail
FileTransportMailDir=var/log/mail
HeaderLineEnding=auto
SmtpTransportServer=
SmtpTransportPort=25
SmtpTransportUser=
SmtpTransportPassword=
SmtpTransportConnectionType=
#SenderEmail=newsletter@example.com
#SenderName=Example Newsletter Sender
#EmailSubjectPrefix=[Newsletter example.com]
ImageInclude=disabled

[BounceSettings]
BounceThresholdValue=3

[NewsletterUserSettings]
UseTplForNameGeneration=disabled
SalutationMappingArray[value_1]=Mr
SalutationMappingArray[value_2]=Ms
RequiredFields[]=salutation
RequiredFields[]=first_name
RequiredFields[]=last_name
AdditionalFields[]
```

Note that `TransportMethodCronjob=file` is the shipped default: queued
newsletters are written to `FileTransportMailDir` and not sent until you set
`smtp` or `sendmail`.

### Extra subscription form fields

```ini
[NewsletterUserSettings]
AdditionalFields[]=company

[AdditionalField_company]
Label=Company
Required=false
Type=string
MaxLenght=100
```

Types are `string`, `text`, `integer`, `select`, `multiselect`, `radio`,
`checkbox`, `date` and `datetime`; the options of each type are documented in
`newsletter.ini` (the keys `MinLenght`/`MaxLenght` are spelled this way in the
code).

### Mail personalization

When a newsletter enables it, the shipped personalization `user_info` replaces `[[name]]`,
`[[salutation_name]]`, `[[first_name]]` and `[[last_name]]` in the subject and
both bodies with the subscriber's data, for example a subject
`Hello [[first_name]], here is our March news`. Add your own like this:

```ini
[NewsletterMailPersonalizations]
AvailableMailPersonalizations[]=user_info
AvailableMailPersonalizations[]=my_customisation

[my_customisation-MailPersonalizationSettings]
Name=My customisation
Class=MyExtensionMyCustomisationMailPersonalization
```

The class implements any of these static methods; each returns the changed
text (see `OWNewsletterUserInfoMailPersonalization` for a working example):

```php
class MyExtensionMyCustomisationMailPersonalization
{
    static function applyOnSubject( $subject, $newsletterUser )
    {
        return str_replace( '[[company]]', 'Example Inc.', $subject );
    }

    static function applyOnHTMLBody( $HTMLBody, $newsletterUser )
    {
        return $HTMLBody;
    }

    static function applyOnPlainTextBody( $plainTextBody, $newsletterUser )
    {
        return $plainTextBody;
    }
}
```

### Tracking

`[NewsletterTracking]` adds a marker to every link (`ClicMarker`, as a query
parameter or an anchor, `ClicMarkerType=param|anchor`) and a read marker before
`</body>` (`ReadMarker`). Markers may use the placeholders `{{DATE=YmdHis}}`,
`{{ATTRIBUTE=identifier}}`, `{{SETTINGS=name}}` (from
`[CustomTrackingSettings]`), `{{LIST_ALIAS}}`, `{{NL_ALIAS}}`, `{{USER_ID}}` and
`{{SITE_URL}}`. `newsletter.ini` holds complete examples for Google Analytics
(`TrackingClass=NewsletterTrackingGoogle`) and XiTi.

## Permissions

Policy functions of the module `newsletter`: `subscribe`, `configure`,
`unsubscribe`, `subscription`, `user`, `view`, `preview`, `archive`, `index`,
`settings`, `send`, `mailbox`, `blacklist`, `subscription_import`,
`subscription_export` and `admin`.

- Visitors need `subscribe`, `configure` and `unsubscribe` (the migration
  grants them to `Anonymous`), and `view` for the web version of an edition.
- Editors need `index` for the tab, plus `send`, `preview` and `user` as they
  should; `mailbox` covers the mailbox and bounce views.

## Cron jobs

The extension registers these cron job parts:

```bash
# everything at once (for testing)
php runcronjobs.php -s <siteaccess> newsletter

# or one by one
php runcronjobs.php -s <siteaccess> newsletter_mailqueue_create      # builds the queue of due sendings
php runcronjobs.php -s <siteaccess> newsletter_mailqueue_process     # sends the queue
php runcronjobs.php -s <siteaccess> newsletter_users_clean_pending   # removes expired pending users
```

A crontab example:

```
*/5 * * * * cd /path/to/exponential && php runcronjobs.php -q -s <siteaccess> newsletter_mailqueue_create newsletter_mailqueue_process
0 3 * * *   cd /path/to/exponential && php runcronjobs.php -q -s <siteaccess> newsletter_users_clean_pending
```

## Upgrading

This is the first release under the se7enxweb name. From an older checkout of
the Open Wide repository: replace the extension directory, regenerate the
autoloads and clear the caches. The database schema and the settings are
unchanged.

## Known limits

- Editions are rendered by `bin/php/createoutput.php`, which logs in as the user
  named `admin`; that user must exist and be enabled.
- `ConfirmationTimeOut` is evaluated as PHP; keep it to numbers and `*`.
- The classes are only shipped as OWMigration migrations; there is no content
  package.

## Troubleshooting

- **Nothing is sent**: check that the cron jobs run for the right siteaccess,
  and that `TransportMethodCronjob` is not `file` (then the mails are in
  `var/log/mail`).
- **"No newsletter available." on the subscription form**: check
  `RootFolderNodeId`, and that a mailing list allows the current siteaccess.
- **Empty mail body**: the template
  `newsletter/skin/<skin>/output/<class identifier>.tpl` is missing for the
  edition's class.
- **Subscribers turn into "bounced"**: they reached `BounceThresholdValue`
  bounces; see `/newsletter/bounce`.

## Contributing

Issues and pull requests are welcome at
<https://github.com/se7enxweb/OWNewsletter>.

## License

GNU General Public License v2.0. See [LICENSE](LICENSE).

## Credits and history

OWNewsletter was written by Open Wide (<http://www.openwide.fr>) from 2013 to
2015 for eZ Publish, the PHP content management system that its creator
discontinued after about 18 years. Parts of it came from the CJW Newsletter
extension by the CJW Network. The admin icons came from the Simplicio set by
neurovit. 7x (<https://se7enx.com>) maintains OWNewsletter today for
Exponential, the continuation of that legacy kernel.

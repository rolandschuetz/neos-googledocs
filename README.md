# CodeQ.GoogleDocs

Google Docs content node type for the Neos CMS.

This is a proof-of-concept implementation and therefore not yet perfect to be used for normal editors. To add content from Google Docs the editor needs to add the Google Docs FileID ( http://prntscr.com/hjdu82 ) manually in the backend. That file will render as HTML format.

# Important Notes

### Installation

You can install the plugin via composer.
```
composer require codeq/googledocs
```

### Settings

Adopt this settings into your site package's Settings.yaml

```
CodeQ:
	GoogleDocs:
		authentication:
			# Google OAuth 2.0 client secret file
			# Place this json file in safe location and provide respective path here.
			# Path should be respective to the DOCUMENT_ROOT\Web folder
			clientSecretFilePath: ~

			# This file will be generate automatically for storing the access token temperory
			# Please provide safe location with file name
			# Path should be respective to the DOCUMENT_ROOT\Web folder
			accessTokenFilePath: ~

			# Google OAuth 2.0 App name
			appName : ~

			# Google OAuth 2.0 Redirect URI
			redirectUri: ~
```

### Enable Google Drive REST API

- Go to the [Google API Console](https://console.developers.google.com/).
- Select a project.
- In the sidebar on the left, expand APIs & auth and select APIs.
- In the displayed list of available APIs, click the link for the Drive API and click Enable API.
- Within the Drive API page, select the Drive UI Integration tab and begin configuring how your App will integrate with the Drive UI.


### Create Credentials

- Go To [Creditials Section](https://console.developers.google.com/apis/credentials).
- Clik on `Create Creditials` Button and select `OAuth Client ID` from the dropdown.
- Select `Web application` from given options.
- Add the name of the App (which will be also used in the Settings `appName`).
- Add `<DOMAIN>/neos/administration/googleDocs` into `Authorized redirect URIs` (replace <DOMAIN> with your actual domain). This URL will also need to be placed into the settings in `redirectUri` option.
- `Authorized JavaScript origins` can be null as it is not used.
- Save all details. It will redirect you to the dashboard.
- Select your entry from the OAuath Client IDs section (https://prnt.sc/hmvbnv).
- You will see the `Download JSON` button on the top (https://prnt.sc/hmvc2q). Click and download that file.
- The download JSON file is the `clientSecretFile` used in our settings. Put that file into the secure place and give the respective path in the settings.


### Backend Module

Backend module is specially used for Authorizing the Google account for accessing the Google Docs File.
You will find the Backend Module `Google Drive Authentication`.
- Go the module and Click on the `Authorize` button.
- You will redirect to the Google Consent Screen. Complete the authorization process and it will redirect you back to the module (if `redirectUri` setting is not properly set, it may give you error when trying to authorize account).
- Once you complete Authorization completely, you will find the `Sign out` button. Click it if you want to de-authorize your account. De-authorizing will stop the file showing in the frontend.

### Use the Google Docs CE

Once all above things are setup successfully, You will find the new CE `Google Docs` in your `content collection` (if it is allowed from the `yaml` configuration of the `Document NodeType`) https://prnt.sc/hmvgd2.

Add that CE into content collection, you will find the Google File ID property. https://prnt.sc/hmvgsq
You can find the Google File ID from its URL, here is the reference about how to find ID https://productforums.google.com/forum/#!category-topic/docs/no/3STOEukh1pU.

Add the Google Docs File ID into the field. Page will automatically reload and you will find the content of Google Docs as HTML format.

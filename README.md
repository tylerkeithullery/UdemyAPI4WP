# UdemyAPI4WP

## Overview

The Udemy API for WordPress plugin allows you to fetch and display Udemy course information on your WordPress site. This plugin integrates with the Udemy Instructor API to retrieve course details and display them in a user-friendly format.

## Features

- Fetch and display Udemy course information.
- Store course data in a custom database table.
- Schedule automatic updates using WordPress cron jobs.
- Generate CSV files of course data.
- Securely store and manage your Udemy API secret token.

![image](https://skillxpand.com/wp-content/uploads/2024/12/Screenshot-2024-12-04-5.23.00-PM.png)


## Installation

1. Download the plugin files and upload them to the `/wp-content/plugins/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Upon activation, the plugin will create a custom database table to store course information.

## Setup

1. Navigate to the "Udemy Course Info" setup page in the WordPress admin menu.
2. Enter your Udemy API secret token in the provided field and click "Save Token".
3. The plugin will redirect you to the setup page if the secret token is not set.

## Usage

### Fetching Course Data

1. Go to the "Udemy Course Info" page in the WordPress admin menu.
2. Click the "Update Table" button to fetch the latest course data from the Udemy API.
3. The course data will be displayed in a table format, showing details such as Course ID, Title, Headline, Paid status, Published status, Reviews, Published Time, Published Title, Rating, URL, and Created date.

### Generating CSV

1. On the "Udemy Course Info" page, click the "Generate CSV" button to download a CSV file containing the course data.

### Automatic Updates

- The plugin schedules a cron job to update the course data twice daily. This ensures that your course information is always up-to-date.

## Admin Pages

### Main Admin Page

- Displays the fetched course data in a table format.
- Provides buttons to manually update the course data and generate a CSV file.

### Setup Page

- Allows you to enter and save your Udemy API secret token.
- Provides instructions on how to create an API client and obtain the secret token.

## Disclaimer

This plugin is in no way endorsed by Udemy in any way.

## Support

For support and further information, please vist [Github](https://github.com/tylerkeithullery/udemyapiwp).

---

This documentation provides an overview of the plugin's functionality and instructions on how to use it effectively. For any issues or questions, please refer to the support section.

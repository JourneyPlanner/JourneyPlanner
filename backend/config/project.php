<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Clockify API Key
    |--------------------------------------------------------------------------
    |
    | This value is the API key for the Clockify API. This key is used to
    | authenticate requests to the Clockify API for the project page.
    |
    */

    "clockify_api_key" => env("CLOCKIFY_API_KEY"),

    /*
    |--------------------------------------------------------------------------
    | Clockify Workspace ID
    |--------------------------------------------------------------------------
    |
    | This value is the workspace ID for the Clockify API. This ID is used to
    | identify the workspace for the project page.
    |
    */

    "clockify_workspace_id" => env("CLOCKIFY_WORKSPACE_ID"),

    /*
    |--------------------------------------------------------------------------
    | Clockify Project ID
    |--------------------------------------------------------------------------
    |
    | This value is the project ID for the Clockify API. This ID is used to
    | identify the project for the project page.
    |
    */

    "clockify_project_id" => env("CLOCKIFY_PROJECT_ID"),

    /*
    |--------------------------------------------------------------------------
    | Jira API Username
    |--------------------------------------------------------------------------
    |
    | This value is the username for the Jira API. This username is used to
    | authenticate requests to the Jira API for the project page.
    |
    */

    "jira_api_username" => env("JIRA_API_USERNAME"),

    /*
    |--------------------------------------------------------------------------
    | Jira API Key
    |--------------------------------------------------------------------------
    |
    | This value is the API key for the Jira API. This key is used to
    | authenticate requests to the Jira API for the project page.
    |
    */

    "jira_api_key" => env("JIRA_API_KEY"),

    /*
    |--------------------------------------------------------------------------
    | Jira Subdomain
    |--------------------------------------------------------------------------
    |
    | This value is the subdomain for the Jira API. This subdomain is used to
    | identify the Jira instance for the project page.
    |
    */

    "jira_subdomain" => env("JIRA_SUBDOMAIN"),

    /*
    |--------------------------------------------------------------------------
    | Jira Board ID
    |--------------------------------------------------------------------------
    |
    | This value is the board ID for the Jira API. This ID is used to
    | identify the board for the project page.
    |
    */

    "jira_board_id" => env("JIRA_BOARD_ID"),
];

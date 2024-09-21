<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class ProjectController extends Controller
{
    private static function getHoursWorked(): int
    {
        $clockifyRequest = Http::withHeaders([
            "X-Api-Key" => config("project.clockify_api_key"),
        ])
            ->withUrlParameters([
                "workspace_id" => config("project.clockify_workspace_id"),
                "project_id" => config("project.clockify_project_id"),
            ])
            ->get(
                "https://api.clockify.me/api/v1/workspaces/{workspace_id}/projects/{project_id}"
            );
        return (new \DateInterval($clockifyRequest->json()["duration"]))->h;
    }

    private static function getCompletedSprintsCount(): int
    {
        $jiraRequest = Http::withBasicAuth(
            config("project.jira_api_username"),
            config("project.jira_api_key")
        )
            ->withUrlParameters([
                "subdomain" => config("project.jira_subdomain"),
                "board_id" => config("project.jira_board_id"),
            ])
            ->get(
                "https://{subdomain}.atlassian.net/rest/agile/1.0/board/{board_id}/sprint"
            );
        return $jiraRequest->json()["total"] - 1;
    }

    private static function getFinishedUserStoriesCount(): int
    {
        $jiraRequest = Http::withBasicAuth(
            config("project.jira_api_username"),
            config("project.jira_api_key")
        )
            ->withUrlParameters([
                "subdomain" => config("project.jira_subdomain"),
                "board_id" => config("project.jira_board_id"),
            ])
            ->get(
                "https://{subdomain}.atlassian.net/rest/agile/1.0/board/{board_id}/issue",
                "jql=statuscategory = Done and hierarchyLevel = 0"
            );
        return $jiraRequest->json()["total"];
    }

    public function getProjectData(): JsonResponse
    {
        return response()->json([
            "time" => self::getHoursWorked(),
            "sprints" => self::getCompletedSprintsCount(),
            "user_stories" => self::getFinishedUserStoriesCount(),
        ]);
    }
}

<?php

namespace TestMonitor\Jira\Tests;

use Mockery;
use TestMonitor\Jira\Client;
use PHPUnit\Framework\TestCase;
use TestMonitor\Jira\Resources\Issue;

class IssuesTest extends TestCase
{
    protected $fields;

    protected $issue;

    protected function setUp(): void
    {
        parent::setUp();

        $project = Mockery::mock('JiraRestApi\Project\Project');
        $project->id = 1;
        $project->key = 'TST';

        $type = Mockery::mock('JiraRestApi\Issue\IssueType');
        $type->id = 1;
        $type->name = 'Bug';

        $this->fields = Mockery::mock('JiraRestApi\Issue\IssueField');
        $this->fields->shouldReceive('getIssueType')->andReturn($type);
        $this->fields->shouldReceive('getProjectKey')->andReturn($project);

        $this->fields->summary = 'Summary';
        $this->fields->description = 'Description';
        $this->fields->issuetype = $type;

        $this->issue = Mockery::mock('\JiraRestApi\Issue\Issue');

        $this->issue->id = '1';
        $this->issue->key = 'TST';
        $this->issue->fields = $this->fields;
    }

    public function tearDown(): void
    {
        Mockery::close();
    }

    /** @test */
    public function it_should_return_a_list_of_issues()
    {
        // Given
        $jira = new Client('url', 'user', 'pass');

        $jira->setIssueService($service = Mockery::mock('JiraRestApi\Issue\IssueService'));

        $results = Mockery::mock('JiraRestApi\Issue\IssueSearchResult');
        $results->issues = [$this->issue];

        $service->shouldReceive('search')->once()->andReturn($results);

        // When
        $issues = $jira->issues('TST');

        // Then
        $this->assertIsArray($issues);
        $this->assertCount(1, $issues);
        $this->assertInstanceOf(Issue::class, $issues[0]);
        $this->assertEquals($issues[0]->id, $this->issue->id);
    }

    /** @test */
    public function it_should_return_a_single_issue()
    {
        // Given
        $jira = new Client('url', 'user', 'pass');

        $jira->setIssueService($service = Mockery::mock('JiraRestApi\Issue\IssueService'));

        $service->shouldReceive('get')->with($this->issue->key)->once()->andReturn($this->issue);

        // When
        $issue = $jira->issue($this->issue->key);

        // Then
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertEquals($this->issue->id, $issue->id);
        $this->assertEquals($this->issue->fields->summary, $issue->summary);
    }

    /** @test */
    public function it_should_create_an_issue()
    {
        // Given
        $jira = new Client('url', 'user', 'pass');

        $jira->setIssueService($service = Mockery::mock('JiraRestApi\Issue\IssueService'));

        $service->shouldReceive('create')->once()->andReturn($this->issue);
        $service->shouldReceive('get')->with($this->issue->key)->once()->andReturn($this->issue);

        // When
        $issue = $jira->createIssue(new Issue('Summary', 'Description', 'Bug', 'TST'));

        // Then
        $this->assertInstanceOf(Issue::class, $issue);
        $this->assertEquals($this->issue->id, $issue->id);
        $this->assertEquals($this->issue->fields->summary, $issue->summary);
    }
}
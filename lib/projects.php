<?php

declare(strict_types=1);

function projects_path(): string
{
    return (string) app_config('storage.projects', dirname(__DIR__) . '/data/projects.json');
}

function projects_all(bool $include_unpublished = false): array
{
    $projects = read_json_file(projects_path(), []);
    $projects = array_values(array_filter($projects, static function (array $project) use ($include_unpublished): bool {
        return $include_unpublished || !empty($project['published']);
    }));

    usort($projects, static function (array $a, array $b): int {
        $orderA = (int) ($a['release_order'] ?? 999);
        $orderB = (int) ($b['release_order'] ?? 999);
        if ($orderA === $orderB) {
            return strcmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? ''));
        }

        return $orderA <=> $orderB;
    });

    return $projects;
}

function project_find(string $id): ?array
{
    foreach (projects_all(true) as $project) {
        if (($project['id'] ?? '') === $id) {
            return $project;
        }
    }

    return null;
}

function project_blank(): array
{
    return [
        'id' => '',
        'title' => '',
        'kicker' => '',
        'status' => 'DRAFT',
        'type' => 'CAMPAIGN',
        'release_order' => 99,
        'summary' => '',
        'body' => '',
        'image' => 'assets/placeholders/project-blank.svg',
        'video_url' => '',
        'artists' => '',
        'shopify_handle' => '',
        'published' => true,
        'featured' => false,
    ];
}

function project_save(array $incoming, ?string $existing_id = null): array
{
    $projects = projects_all(true);
    $title = trim((string) ($incoming['title'] ?? ''));
    $id = trim((string) ($incoming['id'] ?? ''));

    if ($id === '') {
        $id = slugify($title !== '' ? $title : 'project');
    }

    $project = [
        'id' => $id,
        'title' => $title,
        'kicker' => trim((string) ($incoming['kicker'] ?? '')),
        'status' => trim((string) ($incoming['status'] ?? '')),
        'type' => trim((string) ($incoming['type'] ?? 'CAMPAIGN')),
        'release_order' => (int) ($incoming['release_order'] ?? 99),
        'summary' => trim((string) ($incoming['summary'] ?? '')),
        'body' => trim((string) ($incoming['body'] ?? '')),
        'image' => trim((string) ($incoming['image'] ?? 'assets/placeholders/project-blank.svg')),
        'video_url' => trim((string) ($incoming['video_url'] ?? '')),
        'artists' => trim((string) ($incoming['artists'] ?? '')),
        'shopify_handle' => trim((string) ($incoming['shopify_handle'] ?? '')),
        'published' => !empty($incoming['published']),
        'featured' => !empty($incoming['featured']),
        'updated_at' => date('c'),
    ];

    $found = false;
    foreach ($projects as $index => $existing) {
        if (($existing['id'] ?? '') === ($existing_id ?: $id)) {
            $projects[$index] = array_replace($existing, $project);
            $found = true;
            break;
        }
    }

    if (!$found) {
        $project['created_at'] = date('c');
        $projects[] = $project;
    }

    write_json_file(projects_path(), $projects);

    return $project;
}

function project_delete(string $id): bool
{
    $projects = array_values(array_filter(projects_all(true), static fn (array $project): bool => ($project['id'] ?? '') !== $id));

    return write_json_file(projects_path(), $projects);
}

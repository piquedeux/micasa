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
        'title_de' => '',
        'title_en' => '',
        'kicker' => '',
        'kicker_de' => '',
        'kicker_en' => '',
        'status' => 'DRAFT',
        'type' => 'CAMPAIGN',
        'release_order' => 99,
        'summary' => '',
        'summary_de' => '',
        'summary_en' => '',
        'body' => '',
        'body_de' => '',
        'body_en' => '',
        'image' => '',
        'video_url' => '',
        'artists' => '',
        'bigcartel_handle' => '',
        'published' => true,
        'featured' => false,
    ];
}

function project_save(array $incoming, ?string $existing_id = null): array
{
    $projects = projects_all(true);
    $title_de = trim((string) ($incoming['title_de'] ?? ''));
    $title_en = trim((string) ($incoming['title_en'] ?? ''));
    $title = trim((string) ($incoming['title'] ?? ($title_de !== '' ? $title_de : $title_en)));
    $id = trim((string) ($incoming['id'] ?? ''));

    if ($id === '') {
        $id = slugify($title !== '' ? $title : 'project');
    }

    $project = [
        'id' => $id,
        'title' => $title,
        'title_de' => $title_de,
        'title_en' => $title_en,
        'kicker' => trim((string) ($incoming['kicker'] ?? '')),
        'kicker_de' => trim((string) ($incoming['kicker_de'] ?? '')),
        'kicker_en' => trim((string) ($incoming['kicker_en'] ?? '')),
        'status' => trim((string) ($incoming['status'] ?? '')),
        'type' => trim((string) ($incoming['type'] ?? 'CAMPAIGN')),
        'release_order' => (int) ($incoming['release_order'] ?? 99),
        'summary' => trim((string) ($incoming['summary'] ?? '')),
        'summary_de' => trim((string) ($incoming['summary_de'] ?? '')),
        'summary_en' => trim((string) ($incoming['summary_en'] ?? '')),
        'body' => trim((string) ($incoming['body'] ?? '')),
        'body_de' => trim((string) ($incoming['body_de'] ?? '')),
        'body_en' => trim((string) ($incoming['body_en'] ?? '')),
        'image' => trim((string) ($incoming['image'] ?? '')),
        'video_url' => trim((string) ($incoming['video_url'] ?? '')),
        'artists' => trim((string) ($incoming['artists'] ?? '')),
        'bigcartel_handle' => trim((string) ($incoming['bigcartel_handle'] ?? '')),
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

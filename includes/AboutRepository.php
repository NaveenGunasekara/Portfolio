<?php
/**
 * Single-row About section persistence.
 */
declare(strict_types=1);

final class AboutRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function get(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM site_about WHERE id = 1 LIMIT 1');
        $row = $stmt->fetch();
        if (!$row) {
            return [
                'headline'       => 'Creative developer',
                'subtitle'       => 'Building thoughtful digital experiences.',
                'bio'            => 'Tell your story here from the admin panel.',
                'skills'         => 'PHP, MySQL, HTML, UX',
                'image_path'     => 'assets/img/profile-placeholder.svg',
            ];
        }
        return $row;
    }

    public function update(array $data): void
    {
        $sql = 'UPDATE site_about SET headline = :headline, subtitle = :subtitle, bio = :bio, skills = :skills';
        if (!empty($data['image_path'])) {
            $sql .= ', image_path = :image_path';
        }
        $sql .= ', updated_at = NOW() WHERE id = 1';

        $stmt = $this->pdo->prepare($sql);
        $params = [
            'headline' => $data['headline'],
            'subtitle' => $data['subtitle'],
            'bio'      => $data['bio'],
            'skills'   => $data['skills'],
        ];
        if (!empty($data['image_path'])) {
            $params['image_path'] = $data['image_path'];
        }
        $stmt->execute($params);
    }
}

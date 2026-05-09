<?php
/**
 * Music gallery CRUD + ordering.
 */
declare(strict_types=1);

final class MusicRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function allOrdered(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM music_items ORDER BY sort_order ASC, id DESC');
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM music_items WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO music_items (title, artist, description, image_path, external_url, sort_order, created_at, updated_at)
             VALUES (:title, :artist, :description, :image_path, :external_url, :sort_order, NOW(), NOW())'
        );
        $stmt->execute([
            'title'       => $data['title'],
            'artist'      => $data['artist'],
            'description' => $data['description'],
            'image_path'  => $data['image_path'],
            'external_url'=> $data['external_url'],
            'sort_order'  => (int) $data['sort_order'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sql = 'UPDATE music_items SET title = :title, artist = :artist, description = :description,
                external_url = :external_url, sort_order = :sort_order, updated_at = NOW()';
        if (!empty($data['image_path'])) {
            $sql .= ', image_path = :image_path';
        }
        $sql .= ' WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $params = [
            'title'        => $data['title'],
            'artist'       => $data['artist'],
            'description'  => $data['description'],
            'external_url' => $data['external_url'],
            'sort_order'   => (int) $data['sort_order'],
            'id'           => $id,
        ];
        if (!empty($data['image_path'])) {
            $params['image_path'] = $data['image_path'];
        }
        $stmt->execute($params);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('SELECT image_path FROM music_items WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        if ($row && !empty($row['image_path'])) {
            portfolio_delete_upload($row['image_path']);
        }
        $del = $this->pdo->prepare('DELETE FROM music_items WHERE id = :id');
        $del->execute(['id' => $id]);
    }
}

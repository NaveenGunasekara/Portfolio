<?php
/**
 * Contact form submissions.
 */
declare(strict_types=1);

final class MessageRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO contact_messages (name, email, subject, body, ip_address, user_agent, created_at)
             VALUES (:name, :email, :subject, :body, :ip_address, :user_agent, NOW())'
        );
        $stmt->execute([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'subject'    => $data['subject'],
            'body'       => $data['body'],
            'ip_address' => $data['ip_address'],
            'user_agent' => $data['user_agent'],
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function allRecent(int $limit = 200): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT :lim');
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM contact_messages WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM contact_messages WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}

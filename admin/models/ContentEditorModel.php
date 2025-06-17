<?php
class ContentEditorModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllPages() {
        $stmt = $this->pdo->query("SELECT page_id, page_name, content FROM multiple_content");
        $pages = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pages[$row['page_id']] = [
                'name' => $row['page_name'],
                'content' => $row['content']
            ];
        }

        return $pages;
    }

    public function updatePageContent($page_id, $content) {
        $sql = "UPDATE multiple_content SET content = :content WHERE page_id = :page_id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':content' => $content,
            ':page_id' => $page_id
        ]);
    }

    public function getAdminByUsername($username) {
        // Juste pour que AdminController fonctionne même si la table n'existe pas encore
        return ['username' => 'admin'];
    }
}
<?php
class ContentEditorController {
    private $model;

    public function __construct($model) {
        $this->model = $model;
    }

    public function checkAuth() {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header("Location: admin_login.php");
            exit();
        }
    }

    public function getAllPages() {
        return $this->model->getAllPages();
    }

    public function handleFormSubmission($post_data) {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $success = true;
            foreach ($post_data as $page_id => $updated_content) {
                if (!$this->model->updatePageContent($page_id, $updated_content)) {
                    $success = false;
                    break;
                }
            }
            $message = $success ? "Contenu mis à jour avec succès !" : "Erreur lors de la mise à jour.";
        }
        return $message;
    }
}
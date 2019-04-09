<?php


namespace App\Controller\Admin;


use App\Entity\Article;
use App\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CommentController
 * @package App\Controller\Admin
 *
 * @Route("/commentaire")
 */
class CommentController extends AbstractController
{
    /**
     * @Route("/{id}")
     */
    public function index(Article $article)
    {
        return $this->render(
            'admin/comment/index.html.twig',
            [
                'article' => $article
            ]
        );
    }

    /**
     * @Route("/suppression/{id}")
     */
    public function delete(Comment $comment)
    {
        $em = $this->getDoctrine()->getManager();

        $articleId = $comment->getArticle()->getId();

        $em->remove($comment);
        $em->flush();

        $this->addFlash('success', "Le commentaire est supprimé");

        return $this->redirectToRoute(
            'app_admin_comment_index',
            [
                'id' => $articleId
            ]
        );
    }
}

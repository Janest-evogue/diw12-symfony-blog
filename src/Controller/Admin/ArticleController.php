<?php


namespace App\Controller\Admin;


use App\Entity\Article;
use App\Form\ArticleType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ArticleController
 * @package App\Controller\Admin
 *
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function index()
    {
        // afficher tous les articles dans un tableau HTML

        /*
         * Ajouter une colonne avec le nombre de commentaires
         * qui soit un lien clicable vers une page qui liste les commentaires
         * avec la possibilité de les supprimer
         */
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repository->findBy([], ['publicationDate' => 'DESC']);

        return $this->render(
            'admin/article/index.html.twig',
            [
                'articles' => $articles
            ]
        );
    }

    /*
     * Ajouter la méthode edit() qui fait le rendu du formulaire et son traitement
     * Mettre un lien "ajouter" dans la page de liste
     *
     * Validation : tous les champs obligatoires
     *
     * En création :
     * - setter l'auteur avec l'utilisateur connecté
     *  ($this->getUser() depuis un contrôleur)
     * - setter la date de publication à maintenant
     *
     * Adapter la route et le contenu de la méthode pour que la page fonctionne
     * en modification et ajouter un bouton "modifier" dans la page de liste
     *
     * Enregistrer l'article en bdd si le formulaire est bien rempli
     * puis rediriger vers la liste avec un message de confirmation
     */

    /**
     * @Route("/edition/{id}", defaults={"id": null}, requirements={"id": "\d+"})
     */
    public function edit(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $originalImage = null;

        if (is_null($id)) {
            $article = new Article();
            $article
                ->setAuthor($this->getUser())
                // passé dans le constructeur de la classe Article
                //->setPublicationDate(new \DateTime())
            ;
        } else {
            $article = $em->find(Article::class, $id);

            if (is_null($article)) {
                throw new NotFoundHttpException();
            }

            // si l'article contient une image
            if (!is_null($article->getImage())) {
                // nom du fichier venant de la bdd
                $originalImage = $article->getImage();

                // on sette l'image avec un objet File sur l'emplacement de l'image
                // pour le traitement par le formulaire
                $article->setImage(
                    new File($this->getParameter('upload_dir') . $originalImage)
                );
            }
        }

        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var UploadedFile $image */
                $image = $article->getImage();

                // s'il y a eu une image uploadée
                if (!is_null($image)) {
                    // nom sous lequel on va enregistrer l'image
                    $filename = uniqid() . '.' . $image->guessExtension();

                    // déplace l'image uploadée
                    $image->move(
                        // vers le répertoire /public/images
                        // cf config/services.yaml
                        $this->getParameter('upload_dir'),
                        // nom du fichier
                        $filename
                    );

                    // on sette l'attribut image de l'article avec son nom
                    // pour enregistrement en bdd
                    $article->setImage($filename);

                    // en modification on supprime l'ancienne image
                    // s'il y en a une
                    if (!is_null($originalImage)) {
                        unlink($this->getParameter('upload_dir') . $originalImage);
                    }
                } else {
                    // en modification, sans upload, on sette l'attribut image
                    // avec le nom de l'ancienne image
                    $article->setImage($originalImage);
                }

                $em->persist($article);
                $em->flush();

                $this->addFlash('success', "L'article est enregistré");

                return $this->redirectToRoute('app_admin_article_index');
            } else {
                $this->addFlash('error', 'Le formulaire contient des erreurs');
            }
        }

        return $this->render(
            'admin/article/edit.html.twig',
            [
                'form' => $form->createView(),
                'original_image' => $originalImage
            ]
        );
    }

    /**
     * @Route("/suppression/{id}")
     */
    public function delete(Article $article)
    {
        $em = $this->getDoctrine()->getManager();

        // si l'article a une image, on la supprime
        if (!is_null($article->getImage())) {
            unlink($this->getParameter('upload_dir') . $article->getImage());
        }

        $em->remove($article);
        $em->flush();

        $this->addFlash('success', "L'article est supprimé");

        return $this->redirectToRoute('app_admin_article_index');
    }

    /**
     * @Route("/ajax/content/{id}")
     */
    public function ajaxContent(Request $request, Article $article)
    {
        // si la page a été appelée en AJAX
        if ($request->isXmlHttpRequest()) {
            // retour en texte brut
//             return new Response(nl2br($article->getContent()));

            // retour en JSON
//        $response = [
//            'content' => nl2br($article->getContent())
//        ];
//
//        return new JsonResponse($response);


            // retour du rendu d'un template twig
            return $this->render(
                'admin/article/ajax_content.html.twig',
                [
                    'article' => $article
                ]
            );
        } else {
            throw new NotFoundHttpException();
        }

    }
}

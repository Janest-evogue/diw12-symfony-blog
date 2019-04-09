<?php


namespace App\Controller\Admin;


use App\Entity\Category;
use App\Form\CategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CategoryController
 * @package App\Controller\Admin
 *
 * @Route("/categorie")
 */
class CategoryController extends AbstractController
{
    /**
     * L'url de la page est /admin/categorie
     * Le nom de la route est app_admin_category_index
     * @Route("/")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        $repository = $this->getDoctrine()->getRepository(Category::class);
        // équivalent d'un findAll() mais avec un tri sur le nom
        $categories = $repository->findBy([], ['name' => 'ASC']);

        return $this->render(
            'admin/category/index.html.twig',
            [
                'categories' => $categories
            ]
        );
    }

    /**
     * {id} est optionnel, vaut null par défaut et doit être un nombre si renseigné
     * @Route("/edition/{id}", defaults={"id": null}, requirements={"id": "\d+"})
     */
    public function edit(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        if (is_null($id)) { // création
            $category = new Category();
        } else { // modification
            $category = $em->find(Category::class, $id);

            // 404 si l'id reçu dans l'URL n'est pas en bdd
            if (is_null($category)) {
                throw new NotFoundHttpException();
            }
        }

        // création du formulaire relié à la catégorie
        $form = $this->createForm(CategoryType::class, $category);

        //le formulaire analyse la requête
        // et fait le mapping avec l'entité s'il a été soumis
        $form->handleRequest($request);

        dump($category);

        // si le formulaire a été soumis
        if ($form->isSubmitted()) {
            // si les validations à partir des annotations dans
            // l'entité Category sont ok
            if ($form->isValid()) {
                // enregistrement de la catégorie en bdd
                $em->persist($category);
                $em->flush();

                // message de confirmation
                $this->addFlash('success', 'La catégorie est enregistrée');
                // redirection vers la page de liste
                return $this->redirectToRoute('app_admin_category_index');
            } else {
                // message d'erreur
                $this->addFlash('error', 'Le formulaire contient des erreurs');
            }
        }

        return $this->render(
            'admin/category/edit.html.twig',
            [
                // passage du formulaire au template
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/suppression/{id}")
     */
    public function delete(Category $category)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$category->getArticles()->isEmpty()) {
            $this->addFlash(
                'error',
                'La catégorie ne peut être supprimée car elle contient des articles'
            );
        } else {
            $em->remove($category);
            $em->flush();

            $this->addFlash('success', 'La catégorie est supprimée');
        }

        return $this->redirectToRoute('app_admin_category_index');
    }
}

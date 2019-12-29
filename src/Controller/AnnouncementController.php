<?php

namespace App\Controller;

use App\Entity\Announcement;
use App\Entity\Category;
use App\Form\AnnouncementType;
use App\Repository\AnnouncementRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AnnouncementController extends AbstractController
{
    /**
     * @Route("/", name="announcement-list")
     * @param AnnouncementRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(AnnouncementRepository $repository, PaginatorInterface $paginator, Request $request)
    {
        $announcementList = $paginator->paginate(
            $repository->getAll(),
            $request->query->getInt('page', 1),
            15
        );

        //return $this->render('announcement/index.html.twig', [ 'announcementList' => $announcementList,]);

        $params = $this->getTwigParametersWithAside(
            ['announcementList' => $announcementList]
        );
        return $this->render('announcement/index.html.twig', $params);
    }



    /**
     * http://localhost:8000/announcement/new
     * @Route("/announcement/new", name="announcement-create")
     * @Route("/announcement/edit/{id}", name="announcement-edit")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createOrEditAnnouncement(Request $request, $id=null){

        if($id == null){
            $announcement = new Announcement();
        } else {
            $announcement = $this   ->getDoctrine()
                ->getRepository(Announcement::class)
                ->find($id);
        }

        $announcement->setAuthor($this->getUser());

        $form = $this->createForm(AnnouncementType::class, $announcement);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getDoctrine()->getManager();
            $em->persist($announcement);
            $em->flush();

            return $this->redirectToRoute("announcement-list");
        }

        return $this->render("announcement/new.html.twig", [
            "announcementForm" => $form->createView()
        ]);
    }

       /*
     * ETAPE 2: Afficher un SEUL article avec le comentaire
     * Recuperer et Affichage le detail d'un article en fonction de son id
     * on utilise implicitement un param converter
     */


    /**
     * http://localhost:8000/2010
     * @Route("/{id}", name="announcement-details", requirements={"id"="\d+"})
     * @param Announcement $annonce
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function details(Announcement $annonce){
        dump($annonce);
        return $this->render('announcement/details.html.twig', [
            'annonce' => $annonce,
        ]);
    }

    /**
     * http://localhost:8000/by-category/43
     * @Route("/by-category/{id}", name="announcement-by-category")
     * @param Category $category
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showByCategory(Category $category, PaginatorInterface $paginator, Request $request){
         $announcementList = $paginator->paginate(
            $this->getDoctrine()->getRepository(Announcement::class)->getAllByCategory($category),
            $request->query->getInt('page', 1),
            10
        );
        //return $this->render('announcement/index.html.twig', ['announcementList' => $announcementList ]);

        $params = $this->getTwigParametersWithAside(
            ['announcementList' => $announcementList]
        );
        return $this->render('announcement/index.html.twig', $params);
    }
    /*
    public function showByCategory(Category $category){
        $announcementList = $this   ->getDoctrine()
                                    ->getRepository(Announcement::class)
                                    ->getAllByCategory($category)
        ;
        return $this->render('announcement/index.html.twig', ['announcementList' => $announcementList ]);
    }
    */

    private function getTwigParametersWithAside($data){
        $asideData = [
            'categoryList' => $this->getDoctrine()
                ->getRepository(Category::class)
                ->findAll()
        ];
        return array_merge($data, $asideData );
    }



}

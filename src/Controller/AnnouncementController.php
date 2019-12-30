<?php

namespace App\Controller;

use App\Entity\Announcement;
use App\Entity\Category;
use App\Form\AnnouncementType;
use App\Repository\AnnouncementRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

            // Gestion de l'opload des photos
            // recuperation un element saisie dans le furmulaire
            /**
             * pour avoir de autocompletion sur cette method
             * @var UploadedFile $uploadedFile
             */
            $uploadedFile = $form['photoInput']->getData();
            // je fais le traitement lorsque je telechareg une image et je verifie ques c'est bien un bon
            // fichier avec la lecture de l entete
            if ($uploadedFile){
                /*
                // Définition du nom du fichier. guessExtension pour avoir la vrai extention en fonction de
                // l'entête du fichier binaire
                $newFileName = md5(uniqid('photo_')).'.'. $uploadedFile->guessExtension();
                // Déplacement de l'upload dans son dossier de destination
                $uploadedFile->move(
                    $this->getParameter('article.photo.path'),
                    $newFileName
                );
                */


                foreach ($uploadedFile as $fileName){
                    // Définition du nom du fichier. guessExtension pour avoir la vrai extention en fonction de
                    // l'entête du fichier binaire
                    $newFileName = md5(uniqid()).'.'. $fileName->guessExtension();
                    // Déplacement de l'upload dans son dossier de destination
                    $fileName->move(
                        $this->getParameter('article.photo.path'),
                        $newFileName
                    );
                    // Ecrire le nom du fichier dans l'entité
                    $announcement->setPhoto($newFileName);

                    dump($announcement->getPhoto());
                }
                /*
                // Ecrire le nom du fichier dans l'entité
                $announcement->setPhoto($newFileName);
                */
            }

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

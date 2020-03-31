<?php

namespace App\Controller;
use App\Repository\TaskListRepository;
use App\Entity\TaskList;
use App\Services\FileUploader;
// use Symfony\Component\Filesystem\Filesystem;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Request;


class ListController extends AbstractFOSRestController
{
    /**
     * @Route("/", name="list")
     */
    public function index()
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ListController.php',
        ]);
    }

    /**
     * @Rest\Get("/update", name="update")
     */
    public function update()
    {
        return['message' => 'Ini Update']; //ini sama aja keluarannya json karena udah di setting di fos_rest.yaml
    }

    /**
     * @Rest\Delete("/delete", name="delete")
     */
    public function remove()
    {
        // dump('test');die;
    }

    //--------------------------REST API

    /**
     * @Rest\GET("/api/lists", name="get_lists")
     * @return \App\Entity\TaskList[]
     */
    public function List(TaskListRepository $taskListrepository)
    {
        // return $this->handleView($this->view(['list' => $taskListrepository->findAll()])); CARA PERTAMA
        
        $data = $taskListrepository->findAll();
        $count = count($data);
        // dump($count);die;

        if ($count > 0) {
            return $this->view($data, Response::HTTP_OK);
        } else {
        return $this->view($data, Response::HTTP_NO_CONTENT);
        }
    }

    /**
     * @Rest\Post("api/input_post", name = "input_post") 
     * @Rest\RequestParam(name="title", description="Title Of the list", nullable=false)
     * @param ParamFether $paramFetcher
     */
    public function InputPost(ParamFetcher $paramFether)
    {
        $title = $paramFether->get('title');
        dump($title);die;
        if ($title) {
            $list = new TaskList();
            $list->setTitle($title);

            $this->entityManager->persist($list);
            $this->entityManager->flush();

            return $this->view($list, Response::HTTP_CREATED);
        }

        return $this->view(['title' => 'This cannot be null'], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Rest\Post("api/input", name = "input") 
     * @Rest\RequestParam(name="title", description="Title Of the list", nullable=false)
     */
    public function Input_post(Request $request) //    INI PAKE CARA GUA
    {
        $title = $request->get('title');
        $list = new TaskList();
        // dump($title);die;
        if ($title) {
            
            $list->setTitle($title);

            $em = $this->getDoctrine()->getManager();
            $em->persist($list);
            $em->flush();

            return $this->view($list, Response::HTTP_CREATED);
        }

        return $this->view(['title' => 'This cannot be null'], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Rest\GET("/api/show/{id}", name="show_post")
     */
    public function show_post($id, TaskListRepository $tasklist)
    {
        $data = $tasklist->findOneBy(['id'=>$id]);
        return $this->view($data, Response::HTTP_OK);
    }

    /**
     * @Rest\Post("/api/upload", name="upload")
     * @Rest\FileParam(name="image", description="The Background of The List", nullable=false, image=true)
     * @param Request $request
     * @return int
     */
    public function backgroundLists(Request $request)
    {
        $list = new Tasklist();
        //TODO: remove old file if any
        $currentBackground = $list->getBackground();
        if (!is_null($currentBackground)) {
            $filesystem = new Filesystem();
            $filesystem->remove(
                $this->getUploadsDir() . $currentBackground
            );
        }


        /**
         * @var UploadedFile $file
         */
        // var_dump($request->files->get('image'));
        $file = $request->files->get('image');
        $em = $this->getDoctrine()->getManager();

        if ($file) {
            $filename = md5(uniqid()) . '.' . $file->guessClientExtension();

            $file->move(
                $this->getUploadsDir(),
                $filename
            );
            $list->setTitle('di hardcode');
            $list->setBackground($filename);
            $list->setBackgroundPath('/uploads/' . $filename);

            $em->persist($list);
            $em->flush();

            $data = $request->getUriForPath(
                $list->getBackground()
            );

            return $this->view(['Background' => $data, 'Message' => 'Ini berhasil disave'], Response::HTTP_OK);
        }

        return $this->view(['message' => 'Something went wrong'], Response::HTTP_BAD_REQUEST);

        return count($_FILES);
    }

    private function getUploadsDir()
    {
        return $this->getParameter('uploads_dir');
    }
}

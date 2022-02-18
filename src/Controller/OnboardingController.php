<?php

namespace App\Controller;

use App\Entity\Onboarding;
use App\Form\OnboardingType;
use App\Repository\OnboardingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/onboarding")
 */
class OnboardingController extends AbstractController
{
    /**
     * @Route("/", name="onboarding_index", methods={"GET"})
     */
    public function index(OnboardingRepository $onboardingRepository): Response
    {
        return $this->render('onboarding/index.html.twig', [
            'onboardings' => $onboardingRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="onboarding_new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $onboarding = new Onboarding();
        $form = $this->createForm(OnboardingType::class, $onboarding);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($onboarding);
            $entityManager->flush();

            return $this->redirectToRoute('onboarding_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('onboarding/new.html.twig', [
            'onboarding' => $onboarding,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="onboarding_show", methods={"GET"})
     */
    public function show(Onboarding $onboarding): Response
    {
        return $this->render('onboarding/show.html.twig', [
            'onboarding' => $onboarding,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="onboarding_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Onboarding $onboarding, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OnboardingType::class, $onboarding);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('onboarding_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('onboarding/edit.html.twig', [
            'onboarding' => $onboarding,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="onboarding_delete", methods={"POST"})
     */
    public function delete(Request $request, Onboarding $onboarding, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$onboarding->getId(), $request->request->get('_token'))) {
            $entityManager->remove($onboarding);
            $entityManager->flush();
        }

        return $this->redirectToRoute('onboarding_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/rmact", name="onboarding_remote_action", methods={"GET"}, priority="1")
     */
    public function remoteAction(Request $request,OnboardingRepository $OBR,EntityManagerInterface $entityManager): Response
    {
        $email = $request->query->get('email');
//        $vstep = $request->query->get('vstep');
        $nstep = intval($request->query->get('nstep'));
        $onboarding = $OBR->findOneBy(['email'=>$email]);
        if(!$onboarding){
            $onboarding = new Onboarding();
            $onboarding->setEmail($email);
            $onboarding->setStep(0);
            $entityManager->persist($onboarding);
            $entityManager->flush();
            return new Response(json_encode(['status' => 'success']));
        }
        if ($nstep && $nstep > $onboarding->getStep()){
            $onboarding->setStep($nstep);
            $entityManager->persist($onboarding);
            $entityManager->flush();
            return new Response(json_encode(['status' => 'success']));
        }
        return new Response(json_encode(['status' => 'error']));
    }
}

<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\ResendEmailConfirmationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, EmailVerifier $emailVerifier): Response
    {
        $user = new User();
        $user->setRoles(['ROLE_CLIENT']);
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();
            
            $emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('noreply@stubborn.com', 'Stubborn'))
                    ->to(new Address(($user->getEmail())))
                    ->subject('Veuillez confirmer votre adresse email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            $this->addFlash('success', 'Un email de confirmation vous a été envoyé. Veuillez cliquer sur le lien pour activer votre compte');

            return $this->redirectToRoute('app_register');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator, EntityManagerInterface $entityManager,Security $security, EmailVerifier $emailVerifier): Response
    {
        $id = $request->get('id');

        if (!$id || !is_numeric($id)) {
            $this->addFlash('verify_email_error', 'Le lien est invalide ou incomplet');
            return $this->redirectToRoute('app_register');
        }

        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            $this->addFlash('verify_email_error', 'Utilisateur introuvable');
            return $this->redirectToRoute('app_register');
        }

        try {
            $emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        $user->setIsVerified(true);
        $entityManager->flush();

        $security->login($user, 'form_login', 'main');

        $this->addFlash('success', 'Votre adresse email a été vérifiée avec succès.');

        return $this->redirectToRoute('app_home');
    }

    #[Route('/resend-email-confirmation', name: 'app_resend_email_confirmation')]
    public function resendEmailConfirmation(Request $request, EntityManagerInterface $entityManager, EmailVerifier $emailVerifier) : Response
    {
        $form = $this->createForm(ResendEmailConfirmationFormType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if ($user && !$user->isVerified()) {
                try {
                    $emailVerifier->sendEmailConfirmation('app_verify_email', $user, 
                        (new TemplatedEmail())
                            ->from (new Address('noreply@stubborn.com', 'Stubborn'))
                            ->to(new Address($user->getEmail()))
                            ->subject('Veuillez confirmer votre adresse email')
                            ->htmlTemplate('registration/confirmation_email.html.twig')
                    );
                } catch (TransportExceptionInterface $e) {
                    $this->addFlash('verify_email_error', "Erreur lors de l'envoi de l'email. Veuillez réessayer");
                }
            }

            $this->addFlash('success', 'Si cette adresse est associée à un compte, un email de confirmation a été envoyé');
            return $this->redirectToRoute('app_resend_email_confirmation');
        }

        return $this->render('registration/resend_confirmation_email.html.twig', [
            'resendForm' => $form->createView(),
        ]);
    }
}
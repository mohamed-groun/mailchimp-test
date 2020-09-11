<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\MemberType;
use MailchimpMarketing\ApiClient as ApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use MailchimpMarketing\ApiException;

/**
 * @Route("/", name="")
 */
class MailchimpController extends AbstractController
{
    /**
     * @Route("/", name="index_audiences", methods={"GET"})
     */
    public function index()
    {
        $mailchimp = new ApiClient();
        $mailchimp->setApiKey(0,$_ENV['ACCESS_TOKEN']);
        $mailchimp->setConfig([
            'accessToken' => $_ENV['ACCESS_TOKEN'],
            'server' => $_ENV['SERVER']
        ]);
        $audiences = $mailchimp->lists->getAllLists()->lists;
        return $this->render('audiences/index.html.twig', array(
            'audiences' => $audiences,
        ));
    }

    /**
     * @Route("/audience/{idList}", name="index_members", methods={"GET"})
     */
    public function indexMembers(Request $request)
    {
        $mailchimp = new ApiClient();
        $mailchimp->setApiKey(0,$_ENV['ACCESS_TOKEN']);
        $mailchimp->setConfig([
            'accessToken' => $_ENV['ACCESS_TOKEN'],
            'server' => $_ENV['SERVER']
        ]);
        $audience = $mailchimp->lists->getList($request->get('idList'));
        $members = $mailchimp->lists->getListMembersInfo($request->get('idList'))->members;
        return $this->render('audiences/indexMembers.html.twig', array(
            'audience'=> $audience,
            'members' => $members));
    }

    /**
     * @Route("/add/{idList}", name="new_member")
     */
    public function newMember(Request $request)
    {
        $mailchimp = new ApiClient();
        $mailchimp->setApiKey(0, $_ENV['ACCESS_TOKEN']);
        $mailchimp->setConfig([
            'accessToken' => $_ENV['ACCESS_TOKEN'],
            'server' => $_ENV['SERVER']
        ]);
        $audience = $mailchimp->lists->getList($request->get('idList'));
        $member = new Member();

        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $member = $form->getData();
            $json =[
                'email_address' => $member->getEmail(),
                'status'        => $member->getStatus(), // "subscribed","unsubscribed","cleaned","pending"
                'merge_fields'  => [
                    'FNAME'     => $member->getFirstName(),
                    'LNAME'     => $member->getLastName()
                ]
            ];
            try {
                $response = $mailchimp->lists->addListMember($request->get('idList'),$json);
            } catch (ApiException $e) {
                echo $e->getMessage();
            }

            return $this->redirectToRoute('index_members', ['idList' => $request->get('idList')]);
        }
        return $this->render('audiences/new.html.twig', [
            'audience'=> $audience,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/delete/{idList}/{idMember}", name="delete_member", methods={"POST", "GET"})
     */
    public function deleteMember(Request $request)
    {
        $mailchimp = new ApiClient();
        $mailchimp->setApiKey(0, $_ENV['ACCESS_TOKEN']);
        $mailchimp->setConfig([
            'accessToken' => $_ENV['ACCESS_TOKEN'],
            'server' => $_ENV['SERVER']
        ]);
        $audience = $mailchimp->lists->deleteListMember($request->get('idList'), $request->get('idMember'));
        return $this->redirectToRoute('index_members', ['idList' => $request->get('idList')]);

    }
}
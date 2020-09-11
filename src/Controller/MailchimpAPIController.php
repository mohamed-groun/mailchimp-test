<?php

namespace App\Controller;

use MailchimpMarketing\ApiClient;
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
 * @Route("/api", name="api")
 */
class MailchimpAPIController extends AbstractController
{
    /**
     * @Route("/list", name="list", methods={"GET"})
     */
    public function list()
    {
        $mailchimp = new ApiClient();
        $mailchimp->setApiKey(0,'49477f1c81d7e68f5b06bbd4e91528dd-us2');
        $mailchimp->setConfig([
            'accessToken' => $_ENV['ACCESS_TOKEN'],
            'server' => $_ENV['SERVER']
        ]);
        $resp = $mailchimp->lists->getAllLists()->lists;
        return new JsonResponse($resp);
    }

    /**
     * @Route("/list/{idList}", name="getListMembers", methods={"GET"})
     */
    public function getListMembers(Request $request)
    {
        $mailchimp = new ApiClient();
        $mailchimp->setApiKey(0,'49477f1c81d7e68f5b06bbd4e91528dd-us2');
        $mailchimp->setConfig([
            'accessToken' => $_ENV['ACCESS_TOKEN'],
            'server' => $_ENV['SERVER']
        ]);

        $resp = $mailchimp->lists->getListMembersInfo($request->get('idList'))->members;
        return new JsonResponse($resp);
    }

    /**
     * @Route("/addToList/{idList}", name="addToListe", methods={"POST", "GET"})
     */
    public function addToList(Request $request)
    {
        $mailchimp = new ApiClient();
        $mailchimp->setApiKey(0, '49477f1c81d7e68f5b06bbd4e91528dd-us2');
        $mailchimp->setConfig([
            'accessToken' => $_ENV['ACCESS_TOKEN'],
            'server' => $_ENV['SERVER']
        ]);
        $data = $request->getContent();
        $json = json_decode($data);
        $json =[
            'email_address' => $json->email,
            'status'        => $json->status, // "subscribed","unsubscribed","cleaned","pending"
            'merge_fields'  => [
                'FNAME'     => $json->firstname,
                'LNAME'     => $json->lastname
            ]
        ];
        try {
            $response = $mailchimp->lists->addListMember($request->get('idList'),$json);
            dump($response);
            return $response;
        } catch (ApiException $e) {
            echo $e->getMessage();
        }
    }

}
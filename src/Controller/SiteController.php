<?php
// 1) Le namespace correspond au chemin des fichiers dans src/
namespace App\Controller;

// 2) On importe la classe de base pour les contrôleurs
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// 3) On importe Response pour pouvoir renvoyer une réponse HTTP
use Symfony\Component\HttpFoundation\Response;
// 4) On importe l’attribut Route pour définir des URL
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class SiteController extends AbstractController // 5) Notre contrôleur hérite d’AbstractController
{
    // 6) Route pour la page d’accueil (URL "/"), nom interne "home"
    #[Route('/', name: 'home')]
    public function home(): Response // 7) Méthode appelée quand on visite "/"
    {
        // 8) On rend un template Twig "site/home.html.twig" (affichage)
        return $this->render('site/home.html.twig'); // 9) Pas encore de données à transmettre
    }

    // 10) Route pour la liste des articles (URL "/articles")
    #[Route('/articles', name: 'article_index')]
    public function articles(): Response // 11) Méthode pour afficher une liste
    {
        // 12) On simule des données (pas de BDD ici, juste un tableau)
        $articles = [
            ['id' => 1, 'title' => 'Tarte aux pommes', 'excerpt' => 'Une recette simple et gourmande', 'content' => 'Étapes détaillées…'],
            ['id' => 2, 'title' => 'Pâtes au pesto', 'excerpt' => 'Le classique express du soir', 'content' => 'Ingrédients et astuces…'],
        ];

        // 13) On passe les articles au template Twig
        return $this->render('site/articles.html.twig', [
            'articles' => $articles // 14) La variable "articles" sera utilisable dans Twig
        ]);
    }
            // Route pour la page "À propos"
        #[Route('/a-propos', name: 'about')]
        public function about(): Response
        {
            return $this->render('site/about.html.twig');
        }

        #[Route('/contact', name: 'contact')]
        public function contact(Request $request, MailerInterface $mailer): Response
        {
            $form = $this->createForm(ContactType::class);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();

                // Email simulé (DSN null://null en dev)
                $email = (new Email())
                    ->from($data['email'])
                    ->to('contact@pyxelle.test') // adresse fictive
                    ->subject('Nouveau message (site Pyxelle)')
                    ->text(sprintf("De: %s <%s>\n\n%s", $data['name'], $data['email'], $data['message']));

                $mailer->send($email);

                $this->addFlash('success', 'Merci ! Votre message a été “envoyé” (simulation en développement).');
                return $this->redirectToRoute('contact'); // PRG pattern
            }

            return $this->render('site/contact.html.twig', [
                'form' => $form->createView(),
            ]);
        }
        
        #[Route('/meteo', name: 'weather')]
public function weather(Request $request, HttpClientInterface $http): Response
{
    $city = trim((string) $request->query->get('ville', 'Novalaise')); // valeur par défaut
    $error = null;
    $current = null;

    try {
        // 1) Géocodage ville -> latitude/longitude
        $geo = $http->request('GET', 'https://geocoding-api.open-meteo.com/v1/search', [
            'query' => [
                'name' => $city,
                'count' => 1,
                'language' => 'fr',
                'format' => 'json',
            ]
        ])->toArray();

        if (empty($geo['results'][0])) {
            throw new \RuntimeException('Ville introuvable');
        }

        $lat = $geo['results'][0]['latitude'];
        $lon = $geo['results'][0]['longitude'];
        $resolvedName = $geo['results'][0]['name'];
        $country = $geo['results'][0]['country'];

        // 2) Appel météo (current_weather)
        $meteo = $http->request('GET', 'https://api.open-meteo.com/v1/forecast', [
            'query' => [
                'latitude' => $lat,
                'longitude' => $lon,
                'current_weather' => 'true',
                'timezone' => 'auto',
            ]
        ])->toArray();

        if (!isset($meteo['current_weather'])) {
            throw new \RuntimeException('Données météo indisponibles');
        }

        $current = [
            'city' => $resolvedName,
            'country' => $country,
            'temperature' => $meteo['current_weather']['temperature'], // °C
            'windspeed' => $meteo['current_weather']['windspeed'],     // km/h
            'weathercode' => $meteo['current_weather']['weathercode'], // code météo
        ];
    } catch (\Throwable $e) {
        $error = $e->getMessage();
    }

    return $this->render('site/weather.html.twig', [
        'query_city' => $city,
        'current' => $current,
        'error' => $error,
    ]);
}

        // 15) Route pour la page détail (URL "/articles/1", "/articles/2", etc.)
        #[Route('/articles/{id}', name: 'article_show', requirements: ['id' => '\d+'])]
        public function show(int $id): Response // 16) On récupère l'id dans l’URL
        {
            
        // 17) Les mêmes données que plus haut (en vrai on mettrait un repo/BDD)
        $articles = [
            ['id' => 1, 'title' => 'Tarte aux pommes', 'excerpt' => 'Une recette simple et gourmande', 'content' => 'Étapes détaillées…'],
            ['id' => 2, 'title' => 'Pâtes au pesto', 'excerpt' => 'Le classique express du soir', 'content' => 'Ingrédients et astuces…'],
        ];

        // 18) On cherche l’article dont l’id correspond
        $article = null;
        foreach ($articles as $a) { // 19) Boucle simple pour trouver l’article
            if ($a['id'] === $id) {
                $article = $a;
                break;
            }
        }

        // 20) Si pas trouvé → 404 propre
        if (!$article) {
            throw $this->createNotFoundException('Article introuvable');
        }

        // 21) On rend le template détail en lui passant l’article
        return $this->render('site/show.html.twig', [
            'article' => $article
        ]);
    }
}

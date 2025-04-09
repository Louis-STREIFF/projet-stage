Le code sert principalement à trier des artistes provenant d'une base de données Airtable (Artistes).
Il faut d'abord lancer le fichier `index.php` qui est en quelque sorte la page d'accueil du projet.
Tous les artistes sont affichés sans filtre.
`index.php` récupère les données des artistes via la fonction `getArtistesFromAirtable` de `airtable.php`.
C'est à ce moment que tous les artistes sont récupérés sans filtre, encore une fois, il se fera plus tard.
Il y a également différents formulaires pour la recherche par "lieu", "format", "mot dans la bio".
Toutes les recherches se font en direct grâce à la méthode AJAX.
Le formulaire de lieu utilise par ailleurs l'API Google Maps ainsi qu'une fonction JavaScript pour l'autocomplétion (certifie le lieu choisi par l'utilisateur).
Il est possible de choisir plusieurs formats.
La sélection se fait ensuite grâce à `search.php` qui construit la requête qui sera envoyée à Airtable et affiche tous les résultats obtenus grâce à un `foreach()`.
Ces trois fichiers servent donc à faire le tri (ainsi que `script.js`).
Une fois la recherche effectuée, on peut cliquer sur le nom de l'artiste ce qui nous enverra vers `artiste.php`.
Cette page sert juste à afficher l'artiste en question avec une bio plus complète que celle présentée sur la page d'accueil.
Mais il y a également un lien dans la page `index.php` qui renvoie à `ajouter_artiste.php`, ce fichier-ci sert à insérer de nouveaux artistes dans la base de données.
Une fois arrivé sur la page, on a encore une fois des formulaires à remplir.
Après avoir cliqué sur le bouton de validation, les données sont insérées dans une autre table Airtable : "Attentes".
Celle-ci est très similaire à "Artistes" seulement vient se rajouter une colonne "Validé" de type "case à cocher".
Il convient au propriétaire de la base de la cocher ou non s'il considère que les informations sont valides.
Une fois fait, on peut lancer le programme `validation.php` qui vérifiera chaque ligne où la case est validée.
Ensuite, il transférera toutes les données dans la base "Artistes" (base principale) et supprimera la ligne dans la table "Attentes".

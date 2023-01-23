Cette API permet de réaliser des opérations sur la BDD Mediatek86 qui doit être préalablement créée puis remplie avec le script mediatek86.sql.<br />
L'API actuelle fonctonne avec une BDD MySQL en localhost, port 3306, en accès "root" sans pwd, juste pour des tests en local. Pour une mise en production, il faudra modifier les paramètres du fichier AccessBDD.php et sécuriser la BDD avec un user.
Le projet a été fait sous NetBeans.<br />
Pour les tests en local, copier l'API dans le dossier rest_mediatekdocuments (dans le dossier www de wamp ou autre serveur Apache).

<h3>Pour accéder à l'API : </h3>
<code>localhost/rest_mediatekdocuments/</code><br />
(suivi des informations données plus bas)<br />
Si l'accès se fait en direct, les login/pwd vont être demandés.<br />
Si l'accès se fait via Postman, il faut préciser le login/pwd dans basic authentication.<br />
Si l'accès se fait par programme, voir la démarche à suivre dans le wiki du dépôt :<br />
https://github.com/CNED-SLAM/MediaTekDocuments
<br />Accès direct au wiki :<br />
https://github.com/CNED-SLAM/rest_mediatekdocuments/wiki/API-s%C3%A9curis%C3%A9e-en-%22basic-authentication%22

<h3>Les ajouts suivants dans l'URL permettent de réaliser les opérations suivantes :</h3>
Dans les exemples d'url, les informations entre crochets doivent être remplacées par un nom de table ou une valeur d'id (sans mettre les crochets), les informations entre accolades doivent être remplacées par une liste de couples champs/valeur dans les accolades.<br />
<strong>En GET :</strong><br />
<code>[table]</code> // contenu de la table (nom donné sans les crochets)<br />
<code>[table]/[id]</code> // contenu d'une ligne d'une table (excepté pour la table 'exemplaire' : liste des exemplaires d'une revue)<br />
<strong>En POST :</strong><br />
<code>[table]/{champs}</code> // demande d'ajout dans une table, d'un tuple dont les champs sont passés au format json<br />
<strong>En PUT :</strong><br />
<code>[table]/[id]/{champs}</code> // demande d'un tuple (repéré par son id) d'une table, avec les champs à modifier, passés au format json<br />
<strong>En DELETE :</strong><br />
<code>[table]/{champs}</code> // demande de suppression dans une table, pour les tuples répondant à tous les critères  (et) des champs passées au format json<br />

<h3> Résultats obtenus : </h3>
Le résultat est au format json, composé de 3 couples :<br />
<code>code : [code]</code><br />
<code>message : [message]</code><br />
<code>result : {resultat}</code><br />
Codes et messages possibles :<br />
<code>200 "OK"</code><br />
<code>400 "requete invalide"</code><br />
<code>401 "authentification incorrecte"</code><br />
<code>500 "erreur serveur"</code><br />
result contient soit une vide (pour les requêtes autres que GET), soit le résultat du select au format json.

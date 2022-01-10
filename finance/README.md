// TODO
//
// Done : Gestion stratégie
// Done : Gestion connexion
// Done : Implementation strategie par repartition
// Done : Affichage RSI14 mensuel/weekly/daily
// Done : Mise en cache de la page d'accueil 10' ? (infos strat portefeuille personnalisées + infos actifs globaux )
// Done : Calcul automatique via cron des MM, RSI, etc par actif
// Done : Courbe historique sur detail
// Done : Mettre les DM en DB par actif et par jour
// Done : Recupérer les données indicators + DM de la page stock_detail dans DB et ne plus les calculer !!!
// Done : Palmares des meilleures stratégies par methode (DCA/DM) + Niveau de risque
// Done : Arrondie 2/3 chiffres après virgules ou en k en fct min max pour dimuner la taille des data envoyées
// Done : Gestion des portefeuilles en base de donnees
// Done : tags multiples sur expositions ETF (secteurs, geo, ...)

// Choix sur nb actifs investit sur DM (aujourd'hui uniquement le meilleur)
// Recuperer les montants frais EFT + capitalisation + volume xchange par jour/niveau de risque
// Comparaison entre stratégie choisie et investissement réel => Déviation et rattrapage prochain invest 
// Croisement MM200 et TR
// Notification sur journée d'invest + periode ou sur seuil cours avec info nb actions a acheter/vendre en fct stratégie
// Notification si passage cotation ou si gros volume échangé
// ratio de sharpe
// Reinjecte les données json en cache
// Disclaimer + Acceptation cookies
// Grille tarifaire (Notifications/Ajout x stratégie/Gestion x portefeuille/nb portefeuille/synthèse)
// Creer une stack d'execution tache CRON en base de données pour dépiler les actions a réaliser pour n'en ratée aucune et éviter les doublons
// Pause crontab avec lock en gestion admin
// IMPORT/EXPORT ORDRES PORTEFEUILLE

// Idées
// Backlog collective (avec pondération donation)
// Priorisation des demandes
// Rajouter les données : frais de gestion, capitalisation, volume moy echange, dividende ...
// Mettre des likes sur les stratégies

// Abonnement
// Nb de stratégies
// Nb de portefeuille et option synthèse
// Nb d'actifs perso en plus des actifs par défaut
// Comparaison des stratégies par défaut vs perso
// ETF + option Equity

// 8 REGLES D'UN BON INVESTISSEUR

// 1 - On investit que l'argent qu'il reste après avoir couvert ses besoins essentiels et payé ces factures
// 2 - On ne devient pas riche en 1j, l'investissement est sur le moyen/long terme (10, 20, 30 ans)
// 3 - On n'emprunte pas pour investir et on n'investit pas l'argent des autres. Economisez et patientez.
// 4 - Les performances passées ne préjugent pas des performances futures
// 5 - Investir dans ce que l'on comprend. Il faut se former et s'informer pour bien choisir.
// 6 - Définir sa stratégie d'investissement et s'y tenir.
// 7 - Diversifier et investir petit à petit
// 8 - Attention aux commisions et aux marges des conseillers
// 9 - Réinvestir les dividendes

// ERREUR DEBUTANT BOURSE
// Ne jamais moyenner a la baisse (Loosers average losers)
// Manque de diversification ou positions disproportionnées
// Ne jamais suivre les recommandations externes
// Ne pas acheter les actifs surévaluer
// Ne pas investir dans des entreprises/actifs en perte

// Conseil debutant
// Diversifier
// Se faire sa propre opinion
// Réfléchir sur ces stop loss et ces take limit profit

// CALCUL DE LA PERFORMANCE
// https://blog.yomoni.fr/comment-est-calculee-la-performance-chez-yomoni
// https://fr.emmtrade.com/dietz/-Modified-Dietz-Method/


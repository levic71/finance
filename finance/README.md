// TODO
//
// Done : Gestion strat�gie
// Done : Gestion connexion
// Done : Implementation strategie par repartition
// Done : Affichage RSI14 mensuel/weekly/daily
// Done : Mise en cache de la page d'accueil 10' ? (infos strat portefeuille personnalis�es + infos actifs globaux )
// Done : Calcul automatique via cron des MM, RSI, etc par actif
// Done : Courbe historique sur detail
// Done : Mettre les DM en DB par actif et par jour
// Done : Recup�rer les donn�es indicators + DM de la page stock_detail dans DB et ne plus les calculer !!!
// Done : Palmares des meilleures strat�gies par methode (DCA/DM) + Niveau de risque
// Done : Arrondie 2/3 chiffres apr�s virgules ou en k en fct min max pour dimuner la taille des data envoy�es
// Done : Gestion des portefeuilles en base de donnees
// Done : tags multiples sur expositions ETF (secteurs, geo, ...)

// Choix sur nb actifs investit sur DM (aujourd'hui uniquement le meilleur)
// Recuperer les montants frais EFT + capitalisation + volume xchange par jour/niveau de risque
// Comparaison entre strat�gie choisie et investissement r�el => D�viation et rattrapage prochain invest 
// Croisement MM200 et TR
// Notification sur journ�e d'invest + periode ou sur seuil cours avec info nb actions a acheter/vendre en fct strat�gie
// Notification si passage cotation ou si gros volume �chang�
// ratio de sharpe
// Reinjecte les donn�es json en cache
// Disclaimer + Acceptation cookies
// Grille tarifaire (Notifications/Ajout x strat�gie/Gestion x portefeuille/nb portefeuille/synth�se)
// Creer une stack d'execution tache CRON en base de donn�es pour d�piler les actions a r�aliser pour n'en rat�e aucune et �viter les doublons
// Pause crontab avec lock en gestion admin
// IMPORT/EXPORT ORDRES PORTEFEUILLE

// Id�es
// Backlog collective (avec pond�ration donation)
// Priorisation des demandes
// Rajouter les donn�es : frais de gestion, capitalisation, volume moy echange, dividende ...
// Mettre des likes sur les strat�gies

// Abonnement
// Nb de strat�gies
// Nb de portefeuille et option synth�se
// Nb d'actifs perso en plus des actifs par d�faut
// Comparaison des strat�gies par d�faut vs perso
// ETF + option Equity

// 8 REGLES D'UN BON INVESTISSEUR

// 1 - On investit que l'argent qu'il reste apr�s avoir couvert ses besoins essentiels et pay� ces factures
// 2 - On ne devient pas riche en 1j, l'investissement est sur le moyen/long terme (10, 20, 30 ans)
// 3 - On n'emprunte pas pour investir et on n'investit pas l'argent des autres. Economisez et patientez.
// 4 - Les performances pass�es ne pr�jugent pas des performances futures
// 5 - Investir dans ce que l'on comprend. Il faut se former et s'informer pour bien choisir.
// 6 - D�finir sa strat�gie d'investissement et s'y tenir.
// 7 - Diversifier et investir petit � petit
// 8 - Attention aux commisions et aux marges des conseillers
// 9 - R�investir les dividendes

// ERREUR DEBUTANT BOURSE
// Ne jamais moyenner a la baisse (Loosers average losers)
// Manque de diversification ou positions disproportionn�es
// Ne jamais suivre les recommandations externes
// Ne pas acheter les actifs sur�valuer
// Ne pas investir dans des entreprises/actifs en perte

// Conseil debutant
// Diversifier
// Se faire sa propre opinion
// R�fl�chir sur ces stop loss et ces take limit profit

// CALCUL DE LA PERFORMANCE
// https://blog.yomoni.fr/comment-est-calculee-la-performance-chez-yomoni
// https://fr.emmtrade.com/dietz/-Modified-Dietz-Method/


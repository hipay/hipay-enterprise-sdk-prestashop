Feature: Capture & Refund tests

  @capture
  @smoke
  @credit-card
  Scenario: Payer en capture automatique
    Given Je me connecte au panneau d'administration
    And Je veux payer en mode "Hosted Fields"
    And Je veux payer en carte "maestro"
    And J'active le 3DS "Pour toutes les transactions, si disponible"
    And Je veux payer en capture "automatique"

    When J'ajoute "5" T-shirt à mon panier
    And Je valide mon panier
    And Je remplis les informations de facturation
    And Je remplis les informations d'expédition
    And Je sélectionne un mode d'expédition
    And Je sélectionne le mode de paiement "Carte de crédit"
    And Je rentre mes informations de paiement pour la carte "maestro" avec le 3DS "inactif" en mode hosted fields
    And Je paye

    Then La commande est en succès

    When Je sauvegarde le numéro de commande dans "Order#"
    And Je me connecte au BO HiPay
    Then Une transaction existe pour la commande "Order#"
    When J'ouvre la transaction pour la commande "Order#"
    Then La notification "116" existe
    And La notification "117" existe
    And La notification "118" existe

  @capture
  @smoke
  @credit-card
  Scenario: Payer en capture manuelle complète
    Given Je me connecte au panneau d'administration
    And Je veux payer en mode "Hosted Fields"
    And Je veux payer en carte "maestro"
    And J'active le 3DS "Pour toutes les transactions, si disponible"
    And Je veux payer en capture "manuelle"

    When J'ajoute "5" T-shirt à mon panier
    And Je valide mon panier
    And Je remplis les informations de facturation
    And Je remplis les informations d'expédition
    And Je sélectionne un mode d'expédition
    And Je sélectionne le mode de paiement "Carte de crédit"
    And Je rentre mes informations de paiement pour la carte "maestro" avec le 3DS "inactif" en mode hosted fields
    And Je paye

    Then La commande est en succès

    When Je sauvegarde le numéro de commande dans "Order#"
    And J'ouvre la commande "Order#"
    Then La commande est à l'état "En attente d'autorisation (HiPay)"
    And Aucune action HiPay n'est disponible
    When Je me connecte au BO HiPay
    And J'ouvre la transaction pour la commande "Order#"
    Then La notification "116" existe

    When J'envoie la notification "116"
    And J'ouvre la commande "Order#"
    Then La notification "116" est reçue
    And La commande est à l'état "Paiement autorisé (HiPay)"
    And L'action HiPay "Capturer" est disponible

    When Je capture complètement la commande
    And Je me connecte au BO HiPay
    And J'ouvre la transaction pour la commande "Order#"
    Then La notification "118" existe

    When J'envoie la notification "118"
    And J'ouvre la commande "Order#"
    Then La notification "118" est reçue
    And La commande est à l'état "Paiement accepté"

  @capture
  @smoke
  @credit-card
  Scenario: Payer en capture manuelle partielle
    Given Je me connecte au panneau d'administration
    And Je veux payer en mode "Hosted Fields"
    And Je veux payer en carte "maestro"
    And J'active le 3DS "Pour toutes les transactions, si disponible"
    And Je veux payer en capture "manuelle"

    When J'ajoute "5" T-shirt à mon panier
    And Je valide mon panier
    And Je remplis les informations de facturation
    And Je remplis les informations d'expédition
    And Je sélectionne un mode d'expédition
    And Je sélectionne le mode de paiement "Carte de crédit"
    And Je rentre mes informations de paiement pour la carte "maestro" avec le 3DS "inactif" en mode hosted fields
    And Je paye

    Then La commande est en succès

    When Je sauvegarde le numéro de commande dans "Order#"
    And J'ouvre la commande "Order#"
    Then La commande est à l'état "En attente d'autorisation (HiPay)"
    And Aucune action HiPay n'est disponible
    When Je me connecte au BO HiPay
    And J'ouvre la transaction pour la commande "Order#"
    Then La notification "116" existe

    When J'envoie la notification "116"
    And J'ouvre la commande "Order#"
    Then La notification "116" est reçue
    And La commande est à l'état "Paiement autorisé (HiPay)"
    And L'action HiPay "Capturer" est disponible

    When Je capture partiellement "3" objets la commande
    And Je me connecte au BO HiPay
    And J'ouvre la transaction pour la commande "Order#"
    Then La notification "118" existe

    When J'envoie la notification "118"
    And J'ouvre la commande "Order#"
    Then La notification "118" est reçue
    And L'action HiPay "Capturer" est disponible
    And La commande est à l'état "Capture partielle (HiPay)"

    When Je capture partiellement "2" objets la commande
    And Je me connecte au BO HiPay
    And J'ouvre la transaction pour la commande "Order#"
    Then La notification "118" existe

    When J'envoie la notification "118"
    And J'ouvre la commande "Order#"
    Then La notification "118" est reçue

    And La commande est à l'état "Paiement accepté"

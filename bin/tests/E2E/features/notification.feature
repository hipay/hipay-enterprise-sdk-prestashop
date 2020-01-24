Feature: Handle HiPay Notifications

  @payment
  @credit-card
  @notification
  @smoke
  Scenario: Reception des notifications 116 et 118 après une transaction
    Given Je me connecte au panneau d'administration
    And Je veux payer en mode "Hosted Fields"
    And Je veux payer en carte "visa"
    And Je veux payer en capture "automatique"

    When J'ajoute "5" T-shirt à mon panier
    And Je valide mon panier
    And Je remplis les informations de facturation
    And Je remplis les informations d'expédition
    And Je sélectionne un mode d'expédition
    And Je sélectionne le mode de paiement "Carte de crédit"
    And Je rentre mes informations de paiement pour la carte "visa" avec le 3DS "inactif" en mode hosted fields
    And Je paye

    Then La commande est en succès

    When Je sauvegarde le numéro de commande dans "Order#"
    And Je me connecte au BO HiPay
    Then Une transaction existe pour la commande "Order#"

    When J'ouvre la transaction pour la commande "Order#"
    And J'envoie la notification "116"
    And J'ouvre la commande "Order#"
    Then La notification "116" est reçue
    And La commande est à l'état "Paiement autorisé (HiPay)"

    When Je me connecte au BO HiPay
    And J'ouvre la transaction pour la commande "Order#"
    And J'envoie la notification "118"
    And J'ouvre la commande "Order#"
    Then La notification "118" est reçue
    And La commande est à l'état "Paiement accepté"

  @payment
  @credit-card
  @notification
  @smoke
  Scenario: Annulation d'une transaction depuis le BO HiPay
    Given Je me connecte au panneau d'administration
    And Je veux payer en mode "Hosted Fields"
    And Je veux payer en carte "visa"
    And Je veux payer en capture "manuelle"

    When J'ajoute "5" T-shirt à mon panier
    And Je valide mon panier
    And Je remplis les informations de facturation
    And Je remplis les informations d'expédition
    And Je sélectionne un mode d'expédition
    And Je sélectionne le mode de paiement "Carte de crédit"
    And Je rentre mes informations de paiement pour la carte "visa" avec le 3DS "inactif" en mode hosted fields
    And Je paye

    Then La commande est en succès

    When Je sauvegarde le numéro de commande dans "Order#"
    And Je me connecte au BO HiPay
    And J'ouvre la transaction pour la commande "Order#"
    And J'envoie la notification "116"
    And Je clique sur le bouton "Do not capture"
    And Je valide la pop-up de confirmation
    And J'ouvre la transaction pour la commande "Order#"
    Then La notification "175" existe
    When J'envoie la notification "175"
    And J'ouvre la commande "Order#"
    Then La notification "175" est reçue
    And La commande est à l'état "Annulé"
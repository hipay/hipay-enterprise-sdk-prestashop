Feature: Smoke tests

  Scenario Outline: Payer en carte de crédit
    Given Je me connecte au panneau d'administration
    And Je veux payer en mode "Direct Post"
    And Je veux payer en carte "<card>"

    When J'ajoute "5" T-shirt à mon panier
    And Je valide mon panier
    And Je remplis les informations de facturation
    And Je remplis les informations d'expédition
    And Je sélectionne un mode d'expédition
    And Je sélectionne le mode de paiement "Carte de crédit"
    And Je rentre mes informations de paiement pour la carte "<code>"
    And Je paye

    Then La commande est en succès

    When Je sauvegarde le numéro de commande dans "Order#"
    And Je me connecte au BO HiPay
    And J'ouvre la transaction pour la commande "Order#"
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

    Examples:
      | card             | code             |
      | Visa             | visa             |
#    | Mastercard       | mastercard       |
#    | Carte Bancaire   | cb               |
#    | Maestro          | maestro          |
#    | American Express | american-express |

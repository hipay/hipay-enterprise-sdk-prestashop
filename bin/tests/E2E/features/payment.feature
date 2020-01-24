Feature: Payment tests

  @payment
  @credit-card
  @smoke
  Scenario Outline: Payer en carte de crédit en Direct Post
    Given Je me connecte au panneau d'administration
    And Je veux payer en mode "Direct Post"
    And Je veux payer en carte "<card>"
    And J'active le 3DS "Pour toutes les transactions, si disponible"
    And Je veux payer en capture "automatique"

    When J'ajoute "5" T-shirt à mon panier
    And Je valide mon panier
    And Je remplis les informations de facturation
    And Je remplis les informations d'expédition
    And Je sélectionne un mode d'expédition
    And Je sélectionne le mode de paiement "Carte de crédit"
    And Je rentre mes informations de paiement pour la carte "<card>" avec le 3DS "<3DS>"
    And Je paye
    And Je valide la transaction 3DS "<3DS>"

    Then La commande est en succès

    When Je sauvegarde le numéro de commande dans "Order#"
    And Je me connecte au BO HiPay
    Then Une transaction existe pour la commande "Order#"

    Examples:
      | card             | 3DS     |
      | visa             | inactif |
      | visa             | actif   |
      | mastercard       | inactif |
      | mastercard       | actif   |
      | cb               | inactif |
      | maestro          | inactif |
      | american-express | actif   |

  @payment
  @credit-card
  @smoke
  Scenario Outline: Payer en carte de crédit en Hosted Page
    Given Je me connecte au panneau d'administration
    And Je veux payer en mode "Hosted Page"
    And Je veux payer en carte "<card>"
    And J'active le 3DS "Pour toutes les transactions, si disponible"
    And Je veux payer en capture "automatique"

    When J'ajoute "5" T-shirt à mon panier
    And Je valide mon panier
    And Je remplis les informations de facturation
    And Je remplis les informations d'expédition
    And Je sélectionne un mode d'expédition
    And Je sélectionne le mode de paiement "Carte de crédit"
    And Je paye
    And Je rentre mes informations de paiement pour la carte "<card>" avec le 3DS "<3DS>" en mode hosted fields
    And Je paye la page Hosted Page
    And Je valide la transaction 3DS "<3DS>"

    Then La commande est en succès

    When Je sauvegarde le numéro de commande dans "Order#"
    And Je me connecte au BO HiPay
    Then Une transaction existe pour la commande "Order#"

    Examples:
      | card             | 3DS     |
      | visa             | inactif |
      | visa             | actif   |
      | mastercard       | inactif |
      | mastercard       | actif   |
      | cb               | inactif |
      | maestro          | inactif |
      | american-express | actif   |

  @payment
  @credit-card
  @smoke
  Scenario Outline: Payer en carte de crédit en Hosted Fields
    Given Je me connecte au panneau d'administration
    And Je veux payer en mode "Hosted Fields"
    And Je veux payer en carte "<card>"
    And J'active le 3DS "Pour toutes les transactions, si disponible"
    And Je veux payer en capture "automatique"

    When J'ajoute "5" T-shirt à mon panier
    And Je valide mon panier
    And Je remplis les informations de facturation
    And Je remplis les informations d'expédition
    And Je sélectionne un mode d'expédition
    And Je sélectionne le mode de paiement "Carte de crédit"
    And Je rentre mes informations de paiement pour la carte "<card>" avec le 3DS "<3DS>" en mode hosted fields
    And Je paye
    And Je valide la transaction 3DS "<3DS>"

    Then La commande est en succès

    When Je sauvegarde le numéro de commande dans "Order#"
    And Je me connecte au BO HiPay
    Then Une transaction existe pour la commande "Order#"

    Examples:
      | card             | 3DS     |
      | visa             | inactif |
      | visa             | actif   |
      | mastercard       | inactif |
      | mastercard       | actif   |
      | cb               | inactif |
      | maestro          | inactif |
      | american-express | actif   |

  @payment
  @paypal
  @smoke
  Scenario: Payer par Paypal
    Given Je me connecte au panneau d'administration
    And Je veux payer avec le mode de paiement "Paypal"
    And Je veux payer en capture "automatique"

    When J'ajoute "5" T-shirt à mon panier
    And Je valide mon panier
    And Je remplis les informations de facturation
    And Je remplis les informations d'expédition
    And Je sélectionne un mode d'expédition
    And Je sélectionne le mode de paiement "Paypal"
    And Je paye

    Then La page de paiement "Paypal" s'ouvre

    When Je rentre mes informations pour "Paypal"
    And Je paye sur la page Paypal
    Then La commande est en succès

    When Je sauvegarde le numéro de commande dans "Order#"
    And Je me connecte au BO HiPay
    Then Une transaction existe pour la commande "Order#"

    @payment
    @credit-card
    @oneclick
    @smoke
    Scenario: Payer et sauvegarder la carte de crédit pour faire un paiement one-click
      Given Je me connecte au panneau d'administration
      And Je veux payer en mode "Hosted Fields"
      And Je veux payer en carte "mastercard"
      And Je veux payer en capture "automatique"
      And Je veux activer le One-click
      And Je supprime tous les comptes utilisateurs
      And Je m'enregistre sur la boutique

      When J'ajoute "5" T-shirt à mon panier
      And Je valide mon panier
      And Je remplis les informations d'expédition
      And Je sélectionne un mode d'expédition
      And Je sélectionne le mode de paiement "Carte de crédit"
      And Je rentre mes informations de paiement pour la carte "mastercard" avec le 3DS "inactif" en mode hosted fields
      And Je décide de sauvegarder ma carte de crédit

      And Je paye

      Then La commande est en succès

      When Je sauvegarde le numéro de commande dans "Order#"
      And Je me connecte au BO HiPay
      And J'ouvre la transaction pour la commande "Order#"
      And J'envoie la notification "116"

      And J'ajoute "5" T-shirt à mon panier
      And Je valide mon panier
      And Je remplis les informations d'expédition
      And Je sélectionne un mode d'expédition
      And Je sélectionne le mode de paiement "Carte de crédit"

      Then Je vois ma carte "mastercard" avec le 3DS "inactif" sauvegardée sur l'écran

      When Je paye
      Then La commande est en succès

      When Je sauvegarde le numéro de commande dans "Order#"
      And Je me connecte au BO HiPay
      Then Une transaction existe pour la commande "Order#"

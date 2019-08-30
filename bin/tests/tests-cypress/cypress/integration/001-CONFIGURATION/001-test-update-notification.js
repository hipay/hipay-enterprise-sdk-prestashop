/**
 * Functionality tested
 *  - Notification box for new versions on main dashboard
 *  - Notification alert for new versions on configuration screens
 *
 */
var utils = require('../../support/utils');
describe('Update notification box', function () {

    /**
     * Checks when module needs update on the main dashboard
     */
    it('Needs update on main dashboard', function () {
        cy.logToAdmin();
        cy.get('#hipayupdate')
            .should('exist')
            .should('be.visible');
        cy.get('#hipayupdate > .panel-heading')
            .should('contain', 'HiPay Information');
        cy.get('#hipayupdate #hipayNotifLogo')
            .should('exist')
            .should('be.visible');
        cy.get('#hipayupdate p')
            .should('exist')
            .should('be.visible')
            .should(($div) => {
                const text = $div.text();
                expect(text).to.match(/^(\s*)Une nouvelle version du module HiPay Enterprise est disponible\.(\s*)Voir les détails de la version [0-9]+\.[0-9]+\.[0-9]+(\s*)ou(\s*)mettre à jour\.(\s*)$/);
        });
        cy.adminLogOut();
    });

    /**
     * Checks when module needs update on the config page
     */
    it('Needs update on config page', function () {
        cy.logToAdmin();
        cy.goToHipayModuleAdmin();
        cy.get('#hipayupdate')
            .should('exist')
            .should('be.visible')
            .should('have.class', 'alert')
            .should('have.class', 'alert-danger')
            .should(($div) => {
                const text = $div.text();
                expect(text).to.match(/^(\s*)Une nouvelle version du module HiPay Enterprise est disponible\.(\s*)Voir les détails de la version [0-9]+\.[0-9]+\.[0-9]+(\s*)ou(\s*)mettre à jour\.(\s*)$/);
            });
        cy.adminLogOut();
    });
});


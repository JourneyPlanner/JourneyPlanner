describe("create new journey", () => {
    it("passes", () => {
        cy.login();
        cy.visit("/journey/new");
        cy.url().should("include", "/journey/new");

        cy.get("[data-cy='journey-create']").click();
        cy.get("[data-cy='error-journey-name']").should("exist");
        cy.get("[data-cy='journey-name']")
            .should("exist")
            .type("Klassenreise Stockholm");
        cy.get("[data-cy='error-journey-name']").should("not.be.visible");

        cy.get("[data-cy='journey-create']").click();
        cy.get("[data-cy='error-journey-destination']").should("exist");
        cy.get("[data-cy='journey-destination']")
            .should("exist")
            .type("Stockholm");
        cy.get("[data-cy='error-journey-destination']").should("not.exist");

        cy.get("[data-cy='journey-create']").click();
        cy.get("[aria-controls='journey-range-calendar_panel']")
            .should("exist")
            .type("19/08/2024 - 25/08/2024 {enter}");
        cy.get("[data-cy='error-journey-range-calendar']").should(
            "not.be.visible",
        );

        cy.get("[data-cy='journey-create']").click();
        cy.url().should("include", "/dashboard");
    });
});

Cypress.Commands.add("login", () => {
    cy.session(
        "user",
        () => {
            cy.visit("/login");
            cy.fixture("user").then((userFixture) => {
                cy.get("[data-cy='email']").type(userFixture.email);
                cy.get("[data-cy='password']").type(
                    userFixture.password + "{enter}",
                );
                cy.url().should("include", "/dashboard");
            });
        },
        {
            validate: () => {
                cy.getCookie("journeyplanner_session").should("exist");
            },
        },
    );
});

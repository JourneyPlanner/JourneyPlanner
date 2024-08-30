describe("can't access authorized pages", () => {
    it("passes", () => {
        cy.visit("/journey/new");
        cy.url().should("include", "/login");

        cy.visit("/dashboard");
        cy.url().should("include", "/login");
    });
});

pipeline {
    agent any
    stages {
        stage('Build frontend') {
            steps {
                dir('frontend') {
                    def frontend = docker.build("journeyplanner/frontend:latest")
                }
            }
        }
        stage('Build backend') {
            steps {
                dir('../backend') {
                    def backend = docker.build("journeyplanner/backend:latest")
                }
            }
        }
    }
}

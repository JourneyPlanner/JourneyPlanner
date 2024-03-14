pipeline {
    agent any
    stages {
        stage('Build frontend') {
            steps {
                dir('frontend') {
                    docker.build("journeyplanner/frontend:latest")
                }
            }
        }
        stage('Build backend') {
            steps {
                dir('../backend') {
                    docker.build("journeyplanner/backend:latest")
                }
            }
        }
    }
}

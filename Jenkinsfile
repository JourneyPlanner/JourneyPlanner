pipeline {
    agent any
    environment {
        FRONTEND_IMAGE = 'host.docker.internal:5000/journeyplanner/frontend:latest'
        BACKEND_IMAGE = 'host.docker.internal:5000/journeyplanner/backend:latest'
        I18N_IMAGE = 'host.docker.internal:5000/journeyplanner/i18n:latest'
    }
    stages {
        stage('Build Frontend') {
            steps {
                dir('frontend') {
                    script {
                        docker.build(env.FRONTEND_IMAGE)
                    }
                }
            }
        }
        stage('Build Backend') {
            steps {
                dir('backend') {
                    script {
                        docker.build(env.BACKEND_IMAGE)
                    }
                }
            }
        }
        stage('Build i18n Image') {
            steps {
                dir('docker/i18n') {
                    script {
                        docker.build(env.I18N_IMAGE)
                    }
                }
            }
        }
        stage('Deploy') {
            steps {
                script {
                    docker.withRegistry('https://registry.journeyplanner.io', 'docker-registry-credentials') {
                        docker.image(env.FRONTEND_IMAGE).push()
                        docker.image(env.BACKEND_IMAGE).push()
                        docker.image(env.I18N_IMAGE).push()
                    }
                }
            }
        }
    }
}

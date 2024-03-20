pipeline {
    agent any
    environment {
        FRONTEND_IMAGE = 'journeyplanner/frontend:$BUILD_NUMBER'
        BACKEND_IMAGE = 'journeyplanner/backend:$BUILD_NUMBER'
        I18N_IMAGE = 'journeyplanner/i18n:$BUILD_NUMBER'
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
        stage('Push images to Registry') {
            when {
                branch "main"
            }
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
        stage('Deploy') {
            when {
                branch "main"
            }
            steps {
                script {
                    sh 'curl -X POST -H "Content-Type: application/json" -d \'{"stackName":"journeyplanner","prune":true}\' https://portainer.journeyplanner.io/api/stacks/webhooks/112b1dbf-a54a-44ce-8cc1-af24cf84106f'
                }
            }
        }
    }
}

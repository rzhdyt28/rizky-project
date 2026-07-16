module.exports = {
  apps: [
    {
      name: "auto-apply-dashboard",
      script: "src/dashboard/index.js",
      interpreter: "node",
      env: { NODE_ENV: "production" },
    },
    {
      name: "auto-apply-scheduler",
      script: "src/scheduler.js",
      interpreter: "node",
      env: { NODE_ENV: "production" },
    },
  ],
};

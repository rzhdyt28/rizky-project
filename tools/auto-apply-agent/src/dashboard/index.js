import "dotenv/config";
import express from "express";
import path from "path";
import { fileURLToPath } from "url";
import {
  listApplications,
  getApplicationById,
  updateApplicationStatus,
  getStats,
} from "../db/index.js";
import { logger } from "../utils/logger.js";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const app = express();
const PORT = process.env.DASHBOARD_PORT || 3000;

app.set("view engine", "ejs");
app.set("views", path.join(__dirname, "views"));
app.use(express.urlencoded({ extended: true }));
app.use("/drafts", express.static(path.join(process.cwd(), "data", "drafts")));

app.get("/", (req, res) => {
  const { platform, status, scam } = req.query;
  const applications = listApplications({
    platform: platform || undefined,
    status: status || undefined,
    scamStatus: scam || undefined,
    limit: 200,
  });
  const stats = getStats();

  res.render("index", {
    applications,
    stats,
    filters: { platform: platform || "", status: status || "", scam: scam || "" },
  });
});

app.get("/application/:id", (req, res) => {
  const application = getApplicationById(req.params.id);
  if (!application) return res.status(404).send("Lamaran tidak ditemukan.");
  res.render("detail", { application });
});

app.post("/application/:id/status", (req, res) => {
  const { status } = req.body;
  updateApplicationStatus(req.params.id, status);
  res.redirect(`/application/${req.params.id}`);
});

app.listen(PORT, () => {
  logger.success(`Dashboard berjalan di http://localhost:${PORT}`);
});
